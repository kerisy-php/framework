<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Trendi\Di\Base\Compiler;

use Trendi\Di\Base\Definition;
use Trendi\Di\Base\Exception\ServiceNotFoundException;
use Trendi\Di\Base\ContainerInterface;
use Trendi\Di\Base\Reference;
use Trendi\Di\Base\ContainerBuilder;

/**
 * Checks that all references are pointing to a valid service.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class CheckExceptionOnInvalidReferenceBehaviorPass implements CompilerPassInterface
{
    private $container;
    private $sourceId;

    public function process(ContainerBuilder $container)
    {
        $this->container = $container;

        foreach ($container->getDefinitions() as $id => $definition) {
            $this->sourceId = $id;
            $this->processDefinition($definition);
        }
    }

    private function processDefinition(Definition $definition)
    {
        $this->processReferences($definition->getArguments());
        $this->processReferences($definition->getMethodCalls());
        $this->processReferences($definition->getProperties());
    }

    private function processReferences(array $arguments)
    {
        foreach ($arguments as $argument) {
            if (is_array($argument)) {
                $this->processReferences($argument);
            } elseif ($argument instanceof Definition) {
                $this->processDefinition($argument);
            } elseif ($argument instanceof Reference && ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE === $argument->getInvalidBehavior()) {
                $destId = (string) $argument;

                if (!$this->container->has($destId)) {
                    throw new ServiceNotFoundException($destId, $this->sourceId);
                }
            }
        }
    }
}
