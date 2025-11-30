<?php

namespace OCA\Verein\Middleware;

use OCA\Verein\Attributes\RequirePermission;
use OCA\Verein\Service\RBAC\RoleService;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Middleware;
use OCP\ILogger;
use OCP\IUserSession;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

class AuthorizationMiddleware extends Middleware {
    private RoleService $roleService;
    private IUserSession $userSession;
    private ILogger $logger;

    public function __construct(
        RoleService $roleService,
        IUserSession $userSession,
        ILogger $logger
    ) {
        $this->roleService = $roleService;
        $this->userSession = $userSession;
        $this->logger = $logger;
    }

    /**
     * @param object $controller
     */
    public function beforeController($controller, string $methodName) {
        $requirements = $this->collectPermissionAttributes($controller, $methodName);
        if ($requirements === []) {
            return null;
        }

        $user = $this->userSession->getUser();
        if ($user === null) {
            $this->logPermissionViolation('unauthenticated', 'N/A', $controller, $methodName);
            return $this->forbiddenResponse('Authentication required');
        }

        $userId = $user->getUID();
        foreach ($requirements as $requirement) {
            $permission = $requirement->getPermission();
            if (!$this->roleService->userHasPermission($userId, $permission)) {
                $this->logPermissionViolation($userId, $permission, $controller, $methodName);
                return $this->forbiddenResponse(sprintf('Missing permission: %s', $permission));
            }
        }

        return null;
    }

    /**
     * @return RequirePermission[]
     */
    private function collectPermissionAttributes($controller, string $methodName): array {
        $attributes = [];

        try {
            $method = new ReflectionMethod($controller, $methodName);
            foreach ($method->getAttributes(RequirePermission::class) as $attribute) {
                $attributes[] = $attribute->newInstance();
            }
        } catch (ReflectionException $e) {
            $this->logger->debug('RBAC: unable to read method attributes', [
                'controller' => get_class($controller),
                'method' => $methodName,
                'error' => $e->getMessage(),
            ]);
        }

        try {
            $class = new ReflectionClass($controller);
            foreach ($class->getAttributes(RequirePermission::class) as $attribute) {
                $attributes[] = $attribute->newInstance();
            }
        } catch (ReflectionException $e) {
            $this->logger->debug('RBAC: unable to read class attributes', [
                'controller' => get_class($controller),
                'error' => $e->getMessage(),
            ]);
        }

        return $attributes;
    }

    private function forbiddenResponse(string $message): JSONResponse {
        return new JSONResponse([
            'status' => 'error',
            'message' => $message,
        ], 403);
    }

    private function logPermissionViolation(
        string $userId,
        string $permission,
        object $controller,
        string $methodName
    ): void {
        $controllerName = get_class($controller);
        $timestamp = date('Y-m-d H:i:s');
        
        $logEntry = [
            'timestamp' => $timestamp,
            'user' => $userId,
            'permission' => $permission,
            'controller' => $controllerName,
            'method' => $methodName,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'event_type' => 'permission_denied',
        ];

        $this->logger->warning('RBAC: Permission denied', $logEntry);
    }
}
