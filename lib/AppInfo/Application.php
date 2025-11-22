<?php

namespace OCA\Verein\AppInfo;

use OCA\Verein\Db\RoleMapper;
use OCA\Verein\Db\UserRoleMapper;
use OCA\Verein\Middleware\AuthorizationMiddleware;
use OCA\Verein\Service\RBAC\RoleService;
use OCA\Verein\Settings\AdminSection;
use OCA\Verein\Settings\AdminSettings;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\IAppContainer;
use OCP\IGroupManager;
use OCP\ILogger;
use OCP\IUserSession;
use OCP\IURLGenerator;
use OCP\IL10N;

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

        // Register admin settings classes
        $context->registerService(AdminSection::class, function (IAppContainer $c): AdminSection {
            return new AdminSection($c);
        });

        $context->registerService(AdminSettings::class, function (IAppContainer $c): AdminSettings {
            return new AdminSettings($c);
        });
    }

    public function boot(IBootContext $context): void {
        // Nextcloud will auto-discover IIconSection and ISettings implementations
        // This is already handled by the bootstrap mechanism
    }
}
