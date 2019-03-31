<?php

namespace App\Pianissimo\Component\Annotation;

use App\Pianissimo\Component\Container\Container;
use App\Pianissimo\Component\Routing\Annotation\Route;
use BadFunctionCallException;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionProperty;

class AnnotationReader
{
    /** @var Container */
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function getPropertyAnnotations(string $className, string $propertyName, ?string $annotationName = null): array
    {
        if (class_exists($className) === false) {
            throw new BadFunctionCallException(sprintf("Class '%s' not found. Did u forget an use statement?", $className));
        }

        $property = new ReflectionProperty($className, $propertyName);
        $docBlock = $property->getDocComment();

        return $this->docBlockParser($docBlock, $annotationName);
    }

    public function getFunctionAnnotations(string $className, string $functionName, ?string $annotationName = null): array
    {
        if (class_exists($className) === false) {
            throw new BadFunctionCallException(sprintf("Class '%s' not found. Did u forget an use statement?", $className));
        }

        $class = new ReflectionClass($className);
        $method = $class->getMethod($functionName);

        $docBlock = $method->getDocComment();

        return $this->docBlockParser($docBlock, $annotationName);
    }

    private function docBlockParser(string $docBlock, ?string $matchAnnotation = null): array
    {
        // Find all annotations in the DocBlock
        preg_match_all('/@([\\\\\\w]+)\\((?:|(.*?[]"}]))\\)/', $docBlock, $matches);
        $annotationNames = $matches[1];
        $annotationContents = $matches[2];

        $data = [];

        foreach ($annotationNames as $index => $annotationName) {
            // Check whether the annotation name is given, if so, continue if the names don't match.
            if ($matchAnnotation !== null && $annotationName !== $matchAnnotation) {
                continue;
            }

            $annotationContent = $annotationContents[$index];

            // Determine annotation class and make a new instance.
            $annotationClass = $this->getAnnotationClass($annotationName);
            $annotationObject = new $annotationClass();

            // Parse the annotation content and bind it on the annotation object.
            $this->parseAnnotationContent($annotationContent, $annotationObject);

            $data[] = $annotationObject;
        }

        return $data;
    }

    /**
     * TODO improve logic
     */
    private function getAnnotationClass(string $annotationName): string
    {
        $annotations = $this->container->getSetting('annotations');
        return $annotations[$annotationName];
    }

    private function parseAnnotationContent(string $annotationContent, $object): void
    {
        preg_match_all('/([^=,]+)=([^\0]+?)(?=,[^,]+=|$)/', $annotationContent, $result);

        $dataKeys = $result[1];
        $dataValues = $result[2];

        foreach ($dataKeys as $index => $dataKey) {
            $dataKey = trim($dataKey);
            $dataValue = trim($dataValues[$index]);

            $isQuoted = (bool)preg_match('/^(["\']).*\1$/m', $dataValue);

            if ($isQuoted === false && is_numeric($dataValue) === false) {
                throw new InvalidArgumentException('You have an syntax error in an annotation');
            }

            // Remove single/double quotes at the beginning & the end of de value.
            $dataValue = preg_replace('/^(\'(.*)\'|"(.*)")$/', '$2$3', $dataValue);

            if (property_exists($object, $dataKey) === false) {
                $availableOptions = implode(', ', array_keys(get_object_vars($object)));
                throw new InvalidArgumentException(sprintf("The option '%s' is not an valid option. The available options are: %s", $dataKey, $availableOptions));
            }

            $object->$dataKey = $dataValue;
        }
    }
}