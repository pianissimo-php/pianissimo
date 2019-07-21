<?php

namespace Pianissimo\Component\Framework\Loader;

use Pianissimo\Component\DependencyInjection2\Reference;
use Pianissimo\Component\DependencyInjection2\Value;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class YamlFileLoader extends FileLoader
{
    public function load(string $file): void
    {
        $data = Yaml::parseFile($file);

        if ($data === null) {
            return;
        }

        if (array_key_exists('services', $data)) {
            $definitions = $this->handleServices($data['services']);

            foreach ($definitions as $definitionId => $definition) {
                $this->containerBuilder
                    ->add($definitionId, $definition);
            }
        }
    }

    private function handleServices(array $services): array
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

            $definitions[$serviceId] = $definition;
        }

        return $definitions;
    }

    public function supports(): array
    {
        return [
          'yaml'
        ];
    }
}
