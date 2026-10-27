# Руководство по написанию HTTP хендлеров

Правила и паттерны написания HTTP хендлеров. Живые примеры:
`Handler/Auth/Register` (тело запроса), `Handler/Auth/Login` (доменные исключения → коды ответа),
`Handler/GetSurveys` (авторизация, кэш, фабрика),
`Handler/GetSurvey` (параметры пути).

## Структура директорий

Каждый хендлер размещается в отдельной директории вместе со своими DTO:

```
src/App/Http/Handler/
├── Auth/Register/
│   ├── RegisterHandler.php
│   ├── RegisterRequestDto.php
│   └── RegisterResponseDto.php
├── GetSurvey/
│   ├── GetSurveyHandler.php
│   └── GetSurveyRouteParams.php
└── ...
```

## Структура хендлера

### Базовый шаблон

```php
<?php

declare(strict_types=1);

namespace App\Http\Handler\ExampleAction;

use App\Http\RequestDtoFactory\RequestDtoFactory;
use Doctrine\ORM\EntityManager;
use Laminas\Diactoros\Response\JsonResponse;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Post;
use OpenApi\Attributes\RequestBody;
use OpenApi\Attributes\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[Post(
    path: '/api/example-action',
    operationId: 'exampleAction', // Без префикса 'get'
    requestBody: new RequestBody(
        content: new JsonContent(ref: ExampleActionRequestDto::class)
    ),
    tags: ['Example'],
    responses: [
        new Response(
            response: 200,
            description: 'Успешный ответ',
            content: new JsonContent(ref: ExampleActionResponseDto::class)
        ),
        new Response(response: 400, description: 'Неверный запрос'),
        new Response(response: 404, description: 'Не найдено')
    ]
)]
readonly class ExampleActionHandler implements RequestHandlerInterface
{
    public function __construct(
        private EntityManager $entityManager,
        private RequestDtoFactory $dtoFactory,
        // Другие зависимости
    ) {
    }
    
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $dto = $this->dtoFactory->fromParsedBody($request, ExampleActionRequestDto::class);
        
        // Бизнес-логика (если её много — в сервис, хендлер остаётся тонким)
        
        $response = new ExampleActionResponseDto();
        // Заполнение response
        
        return new JsonResponse($response);
    }
}
```

Хендлер не нужно регистрировать ни в роутах, ни в DI: роут появится из атрибута,
а объект соберёт `ReflectionBasedAbstractFactory` по типам аргументов конструктора.
Явная фабрика (`*HandlerFactory`) нужна, только если в конструктор идут скаляры
из конфига — см. `GetSurveysHandlerFactory`.

## OpenAPI атрибуты

### Правила для OpenAPI

1. **Размещение**: OpenAPI атрибуты размещаются НАД классом, а не над методом handle
2. **operationId**: Обязателен, без префикса 'get' (правильно: `surveys`, неправильно: `getSurveys`)
3. **Пути с параметрами**: Используйте синтаксис `:param` вместо `{param}`:
   ```php
   path: '/api/surveys/:id'  // ✓ Правильно
   path: '/api/surveys/{id}' // ✗ Неправильно
   ```
4. **Полные неймспейсы**: В ref используйте полный путь к классу (или импорт)

### Пример с параметрами пути

```php
#[Get(
    path: '/api/surveys/:id',
    operationId: 'survey',
    parameters: [
        new Parameter(
            name: 'id',
            in: 'path',
            required: true,
            schema: new Schema(type: 'integer')
        )
    ],
    responses: [...]
)]
```

Параметры пути валидируются через DTO, реализующий `RouteParamsInterface`:

```php
class GetSurveyRouteParams implements RouteParamsInterface
{
    #[Positive(message: 'ID опроса должен быть положительным числом')]
    public int $id;
}

// в handle():
$params = $this->routeParamsFactory->fromRouteResult($request, GetSurveyRouteParams::class);
```

### Пример с авторизацией

```php
#[Post(
    path: '/api/protected-endpoint',
    operationId: 'protectedAction',
    security: ApiKeySecurity::VALUE,
    requestBody: ...,
    responses: [...]
)]
```

## Request DTO

### Структура Request DTO

```php
<?php

declare(strict_types=1);

namespace App\Http\Handler\ExampleAction;

use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

#[Schema(type: 'object')]
class ExampleActionRequestDto
{
    #[Property(description: 'Email пользователя')]
    #[NotBlank(message: 'Email обязателен')]
    #[Email(message: 'Некорректный формат email')]
    public string $email;
    
    #[Property(description: 'Пароль пользователя')]
    #[NotBlank(message: 'Пароль обязателен')]
    #[Length(min: 8, minMessage: 'Пароль должен содержать минимум {{ limit }} символов')]
    public string $password;
    
    #[Property(description: 'Необязательное поле', nullable: true)]
    public ?string $optionalField = null;
}
```

### Правила валидации

1. **Сообщения на русском**: Все сообщения об ошибках должны быть на русском языке.
   Стандартные сообщения Symfony переводятся автоматически, свои пишем сразу по-русски
2. **Понятные сообщения**: Сообщения должны быть понятны конечному пользователю
3. **Symfony Constraints**: Используйте стандартные констрейнты Symfony; если не хватает —
   свой констрейнт в `src/App/Validator` (пример — `PasswordRequirements`, классовый — `PasswordsMatch`)

## Response DTO

### Структура Response DTO

```php
<?php

declare(strict_types=1);

namespace App\Http\Handler\ExampleAction;

use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;

#[Schema(type: 'object')]
class ExampleActionResponseDto
{
    #[Property(description: 'Сообщение об успехе')]
    public string $message;
    
    #[Property(description: 'Дополнительные данные', nullable: true)]
    public ?array $data = null;
}
```

### Правила для Response

1. **Не добавляйте success: true** - успешность подразумевается по умолчанию
2. **Используйте DTO**: Всегда возвращайте `new JsonResponse(new ResponseDto())`, а не массивы
3. **Инициализация**: Инициализируйте nullable поля значением null

## Обработка ошибок

### Использование ErrorResponseDto

```php
use App\Http\Handler\Common\ErrorResponseDto;

// Для ошибок валидации (обрабатывается автоматически middleware)
// Для других ошибок:
return new JsonResponse(
    new ErrorResponseDto('Пользователь не найден'),
    404
);
```

### Стандартные коды ошибок

- **400** - Ошибка валидации (обрабатывается автоматически)
- **401** - Не авторизован
- **403** - Доступ запрещен
- **404** - Ресурс не найден
- **409** - Конфликт (например, email уже существует)
- **500** - Внутренняя ошибка сервера

## Работа с авторизацией

### Получение текущего пользователя

```php
public function handle(ServerRequestInterface $request): ResponseInterface
{
    $user = $request->getAttribute('user');
    if (!$user instanceof User) {
        return new JsonResponse(
            new ErrorResponseDto('Не авторизован'),
            401
        );
    }
    
    // Работа с пользователем
}
```

## Использование сервисов

### Dependency Injection

```php
public function __construct(
    private readonly EntityManager $entityManager,
    private readonly RequestDtoFactory $dtoFactory,
    private readonly RegisterService $registerService,
    private readonly PanelApiInterface $panelApi,
) {
}
```

### Работа с базой данных

```php
// Получение данных
$user = $this->entityManager->getRepository(User::class)
    ->findOneBy(['email' => $dto->email]);

// Создание и сохранение
$this->entityManager->persist($entity);
$this->entityManager->flush();

// Изменение управляемой сущности — persist не нужен
$user->setActivated(true);
$this->entityManager->flush();
```

## Чек-лист при создании нового хендлера

- [ ] Создана директория `Handler/ActionName/`
- [ ] Создан класс хендлера с суффиксом `Handler`
- [ ] Добавлены OpenAPI атрибуты над классом
- [ ] Указан правильный `operationId` без префикса 'get'
- [ ] Создан `RequestDto` с валидацией на русском языке
- [ ] Создан `ResponseDto` без поля `success`
- [ ] Используется `RequestDtoFactory` для создания DTO
- [ ] Возвращается `JsonResponse` с DTO, а не массивом
- [ ] Обработаны случаи ошибок с правильными HTTP кодами
- [ ] Написан интеграционный тест в `tests/Integration/`

## Важные замечания

1. **Автоматическая регистрация роутов**: Роуты регистрируются автоматически на основе OpenAPI атрибутов
2. **Нет необходимости в ручной регистрации**: Не нужно добавлять роуты в конфигурацию
3. **Middleware pipeline**: Валидация DTO и обработка ошибок происходит автоматически
4. **Кэш роутов**: Вне dev-режима список роутов кэшируется — после добавления хендлера выполните `composer clear-config-cache`
