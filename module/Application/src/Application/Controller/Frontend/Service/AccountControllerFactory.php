<?php

namespace Application\Controller\Frontend\Service;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

use Application\Controller\AccountController as Controller;

class AccountControllerFactory implements FactoryInterface
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('Config');
        $tables = $container->get('TableManager');
        return new Controller(
            $container->get(\Application\Service\UsersService::class),
            $container->get('ChangePasswordForm'),
            $container->get('DeleteUserForm'),
            $container->get('ExternalLoginServiceManager'),
            $config['hosts'],
            $container->get(\Application\Service\SpecificationsService::class),
            $container->get(\Autowp\Message\MessageService::class),
            $container->get(\Autowp\User\Model\UserRename::class),
            $container->get(\Application\Model\UserAccount::class),
            $container->get(\Application\Model\Picture::class),
            $tables->get('login_state'),
            $container->get(\Application\Model\Item::class),
            $container->get(\Autowp\Forums\Forums::class),
            $container->get(\Autowp\User\Model\User::class)
        );
    }
}
