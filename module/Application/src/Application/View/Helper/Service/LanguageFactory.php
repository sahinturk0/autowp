<?php

namespace Application\View\Helper\Service;

use Application\Language;
use Application\View\Helper\Language as Helper;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class LanguageFactory implements FactoryInterface
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param string $requestedName
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): Helper
    {
        return new Helper(
            $container->get(Language::class)
        );
    }
}
