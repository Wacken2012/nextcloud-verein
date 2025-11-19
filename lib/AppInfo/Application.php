<?php

namespace OCA\Verein\AppInfo;

use OCA\Verein\Db\RoleMapper;
use OCA\Verein\Db\UserRoleMapper;
use OCA\Verein\Middleware\AuthorizationMiddleware;
use OCA\Verein\Service\RBAC\RoleService;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\IAppContainer;
use OCP\IGroupManager;
use OCP\ILogger;
use OCP\IUserSession;

class Application extends App implements IBootstrap {
    public const APP_ID = 'verein';

    public function __construct() {
        parent::__construct(self::APP_ID);
    }

    public function register(IRegistrationContext $context): void {
        $context->registerService(RoleService::class, function (IAppContainer $container): RoleService {
            return new RoleService(
                $container->query(RoleMapper::class),
                $container->query(UserRoleMapper::class),
                $container->query(IGroupManager::class),
                $container->query(IUserSession::class),
                $container->query(ILogger::class)
            );
        });

        $context->registerService(AuthorizationMiddleware::class, function (IAppContainer $container): AuthorizationMiddleware {
            return new AuthorizationMiddleware(
                $container->query(RoleService::class),
                $container->query(IUserSession::class),
                $container->query(ILogger::class)
            );
        });

        $context->registerMiddleware(AuthorizationMiddleware::class);
    }

    public function boot(IBootContext $context): void {
        // No-op for now; reserved for future boot-time hooks.
    }
}
