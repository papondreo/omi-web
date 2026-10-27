<?php

declare(strict_types=1);

namespace App\Http\RequestDtoFactory;


use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Translation\Loader\XliffFileLoader;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RequestDtoFactory
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
                    if ($type instanceof \ReflectionNamedType && !$type->isBuiltin()) {
                        $typeName = $type->getName();
                        if ($typeName === \DateTimeInterface::class || $typeName === \DateTime::class || $typeName === \DateTimeImmutable::class) {
                            if (is_string($value)) {
                                try {
                                    $value = new \DateTime($value);
                                } catch (\Exception $e) {
                                    // Let validator handle the error
                                }
                            }
                        }
                    }
                    $object->$key = $value;
                }
            }
        }
        // Валидация объекта после заполнения
        $violations = $this->validator->validate($object);
        if (count($violations) > 0) {
            $messages = [];
            $properties = [];
            foreach ($violations as $violation) {
                $messages[] = $violation->getPropertyPath() . ': ' . $violation->getMessage();
                $properties[$violation->getPropertyPath()] = $violation->getMessage();
            }
            throw new RequestDtoValidationException('Validation failed: ' . implode('; ', $messages), $properties);
        }
        return $object;
    }

    /**
     * @template T
     * @param ServerRequestInterface $request
     * @param class-string<T> $className
     * @return T
     */
    public function fromParsedBody(ServerRequestInterface $request, string $className): object
    {
        $data = $request->getParsedBody() ?: [];
        return $this->createViaReflection($data, $className);
    }

    /**
     * @template T
     * @param ServerRequestInterface $request
     * @param class-string<T> $className
     * @return T
     */
    public function fromQueryParams(ServerRequestInterface $request, string $className): object
    {
        $data = $request->getQueryParams() ?: [];
        return $this->createViaReflection($data, $className);
    }

    /**
     * @template T
     * @param ServerRequestInterface $request
     * @param class-string<T> $className
     * @return T
     */
    public function fromMultipartData(ServerRequestInterface $request, string $className): object
    {
        $data = $request->getParsedBody() ?: [];
        $uploadedFiles = $request->getUploadedFiles();
        
        // Объединяем данные формы с загруженными файлами
        foreach ($uploadedFiles as $key => $file) {
            $data[$key] = $file;
        }
        
        return $this->createViaReflection($data, $className);
    }
}
