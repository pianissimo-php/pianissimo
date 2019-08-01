<?php

namespace Pianissimo\Component\Framework\Loader;

use Pianissimo\Component\DependencyInjection\Definition;
use Pianissimo\Component\DependencyInjection\Reference;
use Pianissimo\Component\DependencyInjection\Value;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class YamlFileLoader extends FileLoader
{
    /**
     * @var string
     */
    private $file;

    public function load(string $file): void
    {
        $this->file = $file;

        $data = Yaml::parseFile($file);

        if ($data === null) {
            return;
        }

        if (array_key_exists('parameters', $data)) {
            $this->handleParameters($data['parameters']);
        }

        if (array_key_exists('services', $data)) {
            $this->handleServices($data['services']);
        }

        if (array_key_exists('compiler_passes', $data)) {
            $this->handleCompilerPasses($data['compiler_passes']);
        }
    }

    private function handleParameters(array $parameters): void
    {
        // Add the parameters to the ContainerBuilder.
        foreach ($parameters as $parameterKey => $parameterValue) {
            $this->containerBuilder->setParameter($parameterKey, $parameterValue);
        }
    }

    private function handleServices(array $services): void
    {
        $definitions = [];

        foreach ($services as $serviceId => $service) {
            if (is_string($service)) {
                if ($service[0] !== '@') {
                    throw new ParseException(sprintf("Service definition '%s' must be a definition or a reference", $serviceId));
                }

                $definitions[$serviceId] = new Reference(substr($service, 1));
                continue;
            }

            if (array_key_exists('resource', $service)) {
                $_definitions = $definitions;
                $definitions = array_merge($_definitions, $this->handleServiceResource($service));
                continue;
            }

            $class = $serviceId;

            if (array_key_exists('class', $service)) {
                $class = $service['class'];
            }

            $definition = $this->containerBuilder
                ->register($serviceId, $class);

            if (array_key_exists('autowire', $service)) {
                $definition->setAutowired((bool) $service['autowire']);
            }

            if (array_key_exists('arguments', $service)) {
                $arguments = $service['arguments'];

                if (gettype($arguments) !== gettype([])) {
                    throw new ParseException(sprintf("Value of 'arguments' of service definition of class '%s' must be an collection", $class));
                }

                foreach ($arguments as $argument) {
                    if (is_string($argument)) {
                        if (strpos($argument, '@') === 0) {
                            $definition->addArgument(new Reference(substr($argument, 1)));
                            continue;
                        }

                        $definition->addArgument(new Value($argument));
                    }
                }
            }

            if (array_key_exists('tags', $service)) {
                if (gettype($service['tags']) !== gettype([])) {
                    throw new ParseException(sprintf("Value of 'tags' of service definition of class '%s' must be an collection", $service));
                }

                foreach ($service['tags'] as $tag) {
                    $definition->addTag($tag);
                }
            }

            $definitions[$serviceId] = $definition;
        }

        // Add the service definitions to the ContainerBuilder.
        foreach ($definitions as $definitionId => $definition) {
            $this->containerBuilder->add($definitionId, $definition);
        }
    }

    private function handleServiceResource(array $service): array
    {
        $definitions = [];

        $resource = $service['resource'];
        $files = glob($resource);

        foreach ($files as $file) {
            $class = str_replace(['.php', '../src/', '/'], ['', 'App\\', '\\'], $file);

            $definition = new Definition($class);
            $definition->setAutowired(true);

            if (array_key_exists('tags', $service)) {
                if (gettype($service['tags']) !== gettype([])) {
                    throw new ParseException(sprintf("Value of 'tags' of service definition of class '%s' must be an collection", $service));
                }

                foreach ($service['tags'] as $tag) {
                    $definition->addTag($tag);
                }
            }

            $definitions[$class] = $definition;
        }

        return $definitions;
    }

    private function handleCompilerPasses(array $compilerPasses): void
    {
        if (gettype($compilerPasses) !== gettype([])) {
            throw new ParseException(sprintf("Value of 'compiler_passes' of service definition of class '%s' must be an collection", $class));
        }

        foreach ($compilerPasses as $compilerPass) {
            $instance = new $compilerPass();
            $this->containerBuilder->addCompilerPass($instance);
        }
    }

    public function supports(): array
    {
        return [
          'yaml',
        ];
    }
}
