<?php

namespace Application\Service;

use Application\DuplicateFinder;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class DuplicateFinderFactory implements FactoryInterface
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param string $requestedName
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): DuplicateFinder
    {
        $tables = $container->get('TableManager');
        return new DuplicateFinder(
            $container->get('RabbitMQ'),
            $tables->get('df_distance')
        );
    }
}
