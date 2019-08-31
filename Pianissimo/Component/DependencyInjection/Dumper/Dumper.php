<?php

namespace Pianissimo\Component\DependencyInjection\Dumper;

use Exception;
use Pianissimo\Component\DependencyInjection\ContainerBuilder;
use Pianissimo\Component\DependencyInjection\Definition;
use Pianissimo\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class Dumper
{
    /**
     * @var ContainerBuilder
     */
    private $containerBuilder;

    /**
     * @var string[]|array
     */
    private $methods;

    /**
     * @var string|null
     */
    private $parameterBagMethodName;

    public function dump(ContainerBuilder $containerBuilder, $file = null): string
    {
        $this->containerBuilder = $containerBuilder;

        $methods = $this->getServiceMethods();
        $serviceIds = $this->getServiceIds();
        $getters = $this->getGetters();

        $parameterBagInterfaceLoader = '';

        if ($this->parameterBagMethodName !== null) {
            $parameterBagInterfaceLoader = sprintf("\$this->$this->parameterBagMethodName();");
        }

        $code = <<<EOD
<?php

use Pianissimo\Component\DependencyInjection\Container;
use Pianissimo\Component\DependencyInjection\Exception\ServiceNotFoundException;

final class CachedContainer extends Container
{
    private \$methods = [];

    private \$serviceIds = [];

    public function __construct()
    {
        parent::__construct();

$methods

$serviceIds
        $parameterBagInterfaceLoader
    }
    
$getters
    /**
     * @throws ServiceNotFoundException
     */
    public function get(\$id)
    {
        \$id = \$this->getServiceId(\$id);
    
        if (array_key_exists(\$id, \$this->services) === true) {
            return \$this->services[\$id];
        }
        
        if (\$this->has(\$id) === true) {
            \$method = \$this->methods[\$id];
            return \$this->\$method();
        }

        throw new ServiceNotFoundException(sprintf("Service '%s' not found", \$id));
    }

    public function has(\$id): bool
    {
        \$id = \$this->getServiceId(\$id);

        return array_key_exists(\$id, \$this->methods);
    }
    
    private function getServiceId(string \$id)
    {
        if (array_key_exists(\$id, \$this->serviceIds) === true) {
            return \$this->serviceIds[\$id];
        }
        
        return \$id;
    }
}
EOD;
        if ($file !== null) {
            file_put_contents($file, $code);

            return $file;
        }

        return $code;
    }

    private function getParameters(): string
    {
        $parameterBag = $this->containerBuilder->get(ParameterBagInterface::class);

        $data = '';

        foreach ($parameterBag as $key => $value) {
            $data .= <<<EOD
        \$instance->set('$key', '$value');

EOD;
        }

        return $data;
    }

    private function getServiceMethods(): string
    {
        $definitions = $this->containerBuilder->getDefinitions();

        $data = '';
        $number = 0;

        foreach ($definitions as $id => $definition) {
            $methodName = '_' . sprintf('%04d', $number);

            $this->methods[$id] = $methodName;

            if ($id === ParameterBagInterface::class) {
                $this->parameterBagMethodName = $methodName;
            }

            $data .= <<<EOD
        \$this->methods['$id'] = '$methodName';

EOD;
            $number++;
        }

        return $data;
    }

    private function getServiceIds(): string
    {
        $definitions = $this->containerBuilder->getDefinitions();

        $data = '';

        foreach ($definitions as $id => $definition) {
            $class = $definition->getClass();

            $data .= <<<EOD
        \$this->serviceIds['$class'] = '$id';

EOD;
        }

        return $data;
    }

    private function getGetters(): string
    {
        $definitions = $this->containerBuilder->getDefinitions();

        $data = '';

        $number = 0;

        foreach ($definitions as $id => $definition) {
            $methodName = '_' . sprintf('%04d', $number);
            $class = $definition->getClass();

            $arguments = $this->getInstanceArguments($definition);
            $methodCalls = $this->getMethodCalls($definition);

            $methodContent = <<<EOD
\$instance = new \\$class($arguments);
$methodCalls
        \$this->services['$id'] = \$instance;

        return \$instance;
EOD;

            if ($id === ParameterBagInterface::class) {
                $parameters = $this->getParameters();

                $methodContent = <<<EOD
\$instance = \$this->parameterBag;
        \$this->services['$id'] = \$instance;

$parameters
        return \$instance;
EOD;
            }

            $data .= <<<EOD
    private function $methodName(): \\$class
    {
        $methodContent
    }


EOD;

            $number++;
        }

        return $data;
    }

    private function getInstanceArguments(Definition $definition): string
    {
        $arguments = [];

        foreach ($definition->getArguments() as $argument) {
            $class = (string) $argument;

            if (array_key_exists($class, $this->methods) === false) {
                if (class_exists($class) === true) {
                    throw new Exception('Error');
                }

                $arguments[] = $class;
                continue;
            }

            $arguments[] = "\$this->get('$class')";
        }

        return implode(', ', $arguments);
    }

    private function getMethodCalls(Definition $definition): string
    {
        $data = '';

        foreach ($definition->getMethodCalls() as $methodCall) {
            $method = $methodCall->getMethod();

            $arguments = [];

            foreach ($methodCall->getArguments() as $argument) {
                $arguments[] = sprintf('"%s"', $argument);
            }

            $arguments = implode(', ', $arguments);

            $data .= <<<EOD
        \$instance->$method($arguments);

EOD;
        }

        return $data;
    }
}
