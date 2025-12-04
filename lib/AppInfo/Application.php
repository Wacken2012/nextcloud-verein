<?php

namespace OCA\Verein\AppInfo;

use OCA\Verein\Db\RoleMapper;
use OCA\Verein\Db\UserRoleMapper;
use OCA\Verein\Db\MemberMapper;
use OCA\Verein\Db\FeeMapper;
use OCA\Verein\Middleware\AuthorizationMiddleware;
use OCA\Verein\Service\RBAC\RoleService;
use OCA\Verein\Service\Export\CsvExporter;
use OCA\Verein\Service\Export\PdfExporter;
use OCA\Verein\Service\MemberService;
use OCA\Verein\Service\FeeService;
use OCA\Verein\Service\StatisticsService;
use OCA\Verein\Service\ReminderService;
use OCA\Verein\Service\PrivacyService;
use OCA\Verein\Settings\AdminSection;
use OCA\Verein\Settings\AdminSettings;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\IAppContainer;
use OCP\IGroupManager;
use OCP\IUserSession;
use Psr\Log\LoggerInterface;
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
                $container->query(IUserSession::class)
            );
        });

        $context->registerService(AuthorizationMiddleware::class, function (IAppContainer $container): AuthorizationMiddleware {
            return new AuthorizationMiddleware(
                $container->query(RoleService::class),
                $container->query(IUserSession::class),
                $container->query(LoggerInterface::class)
            );
        });

        // Register export services
        $context->registerService(CsvExporter::class, function (IAppContainer $container): CsvExporter {
            return new CsvExporter();
        });

        $context->registerService(PdfExporter::class, function (IAppContainer $container): PdfExporter {
            return new PdfExporter();
        });

        // Register member and fee services
        $context->registerService(MemberService::class, function (IAppContainer $container): MemberService {
            return new MemberService(
                $container->query(MemberMapper::class)
            );
        });

        $context->registerService(FeeService::class, function (IAppContainer $container): FeeService {
            return new FeeService(
                $container->query(FeeMapper::class)
            );
        });

        $context->registerService(StatisticsService::class, function (IAppContainer $container): StatisticsService {
            return new StatisticsService(
                $container->query(MemberMapper::class),
                $container->query(FeeMapper::class)
            );
        });

        // Register Reminder and Privacy services
        $context->registerService(ReminderService::class, function (IAppContainer $container): ReminderService {
            return new ReminderService(
                null, // ReminderMapper
                null, // IMailer
                $container->query(LoggerInterface::class),
                null, // MemberService
                null  // SettingService
            );
        });

        $context->registerService(PrivacyService::class, function (IAppContainer $container): PrivacyService {
            return new PrivacyService();
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
