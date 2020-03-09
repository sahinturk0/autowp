<?php

namespace Application\Model\Service;

use Application\Model\Categories;
use Application\Model\Item;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class CategoriesFactory implements FactoryInterface
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param string $requestedName
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): Categories
    {
        $tables = $container->get('TableManager');
        return new Categories(
            $container->get('HttpRouter'),
            $tables->get('item'),
            $container->get(Item::class)
        );
    }
}
