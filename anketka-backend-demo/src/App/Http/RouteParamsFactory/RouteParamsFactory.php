<?php

declare(strict_types=1);

namespace App\Http\RouteParamsFactory;

use Laminas\Diactoros\Response\JsonResponse;
use Mezzio\Router\RouteResult;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Translation\Loader\XliffFileLoader;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Http\Handler\Common\ErrorResponseDto;

class RouteParamsFactory
{
    private ValidatorInterface $validator;

    public function __construct()
    {
        $translator = new Translator('ru');
        $translator->addLoader('xlf', new XliffFileLoader());
        $validatorTranslationFile = dirname(__DIR__, 4) . '/vendor/symfony/validator/Resources/translations/validators.ru.xlf';
        if (file_exists($validatorTranslationFile)) {
            $translator->addResource(
                'xlf',
                $validatorTranslationFile,
                'ru',
                'validators'
            );
        }

        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->setTranslator($translator)
            ->setTranslationDomain('validators')
            ->getValidator();
    }

    /**
     * @template T
     * @param array $data
     * @param class-string<T> $className
     * @return T
     */
    private function createViaReflection(array $data, string $className): object
    {
        $reflectionClass = new \ReflectionClass($className);
        $object = $reflectionClass->newInstanceWithoutConstructor();
        foreach ($data as $key => $value) {
            if ($reflectionClass->hasProperty($key)) {
                $property = $reflectionClass->getProperty($key);
                if ($property->isPublic()) {
                    $type = $property->getType();
                    if ($type instanceof \ReflectionNamedType) {
                        $typeName = $type->getName();
                        if ($typeName === 'int' && is_string($value)) {
                            $value = (int) $value;
                        } elseif ($typeName === 'float' && is_string($value)) {
                            $value = (float) $value;
                        } elseif ($typeName === 'bool' && is_string($value)) {
                            $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                        }
                    }
                    $object->$key = $value;
                }
            }
        }
        
        $violations = $this->validator->validate($object);
        if (count($violations) > 0) {
            $messages = [];
            $properties = [];
            foreach ($violations as $violation) {
                $messages[] = $violation->getPropertyPath() . ': ' . $violation->getMessage();
                $properties[$violation->getPropertyPath()] = $violation->getMessage();
            }
            throw new RouteParamsValidationException('Validation failed: ' . implode('; ', $messages), $properties);
        }
        return $object;
    }

    /**
     * @template T
     * @param ServerRequestInterface $request
     * @param class-string<T> $className
     * @return T
     * @throws RouteParamsValidationException
     */
    public function fromRouteResult(ServerRequestInterface $request, string $className): object
    {
        /** @var RouteResult|null $routeResult */
        $routeResult = $request->getAttribute(RouteResult::class);
        
        if (!$routeResult) {
            throw new RouteParamsValidationException('No route result found', []);
        }
        
        $data = $routeResult->getMatchedParams();
        return $this->createViaReflection($data, $className);
    }
}