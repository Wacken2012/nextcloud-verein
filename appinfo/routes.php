<?php
return [
    'routes' => [
        // Dashboard page
        ['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],

        // Dashboard data endpoints
        ['name' => 'member#index', 'url' => '/members', 'verb' => 'GET'],
        ['name' => 'member#create', 'url' => '/members', 'verb' => 'POST'],
        ['name' => 'member#show', 'url' => '/members/{id}', 'verb' => 'GET'],
        ['name' => 'member#update', 'url' => '/members/{id}', 'verb' => 'PUT'],
        ['name' => 'member#destroy', 'url' => '/members/{id}', 'verb' => 'DELETE'],
        
        ['name' => 'finance#index', 'url' => '/finance', 'verb' => 'GET'],
        ['name' => 'finance#create', 'url' => '/finance', 'verb' => 'POST'],
        ['name' => 'finance#update', 'url' => '/finance/{id}', 'verb' => 'PUT'],
        ['name' => 'finance#destroy', 'url' => '/finance/{id}', 'verb' => 'DELETE'],
        
        ['name' => 'sepa#export', 'url' => '/sepa/export', 'verb' => 'GET'],

        // Export endpoints
        ['name' => 'export#exportMembersAsCsv', 'url' => '/export/members/csv', 'verb' => 'GET'],
        ['name' => 'export#exportMembersAsPdf', 'url' => '/export/members/pdf', 'verb' => 'GET'],
        ['name' => 'export#exportFeesAsCsv', 'url' => '/export/fees/csv', 'verb' => 'GET'],
        ['name' => 'export#exportFeesAsPdf', 'url' => '/export/fees/pdf', 'verb' => 'GET'],

        // Statistics endpoints
        ['name' => 'statistics#getMemberStatistics', 'url' => '/statistics/members', 'verb' => 'GET'],
        ['name' => 'statistics#getFeeStatistics', 'url' => '/statistics/fees', 'verb' => 'GET'],

        // RBAC & permissions
        ['name' => 'role#index', 'url' => '/roles', 'verb' => 'GET'],
        ['name' => 'role#store', 'url' => '/roles', 'verb' => 'POST'],
        ['name' => 'role#show', 'url' => '/roles/{id}', 'verb' => 'GET'],
        ['name' => 'role#update', 'url' => '/roles/{id}', 'verb' => 'PUT'],
        ['name' => 'role#destroy', 'url' => '/roles/{id}', 'verb' => 'DELETE'],
        ['name' => 'role#indexByClubType', 'url' => '/roles/club/{clubType}', 'verb' => 'GET'],
        ['name' => 'role#getUserRoles', 'url' => '/roles/users/{userId}', 'verb' => 'GET'],
        ['name' => 'role#assignRole', 'url' => '/roles/users', 'verb' => 'POST'],
        ['name' => 'role#removeRoles', 'url' => '/roles/users', 'verb' => 'DELETE'],

        ['name' => 'permission#index', 'url' => '/permissions', 'verb' => 'GET'],
        ['name' => 'rolesApi#getPermissions', 'url' => '/api/v1/permissions', 'verb' => 'GET'],

        // Backwards compatibility: Old API paths (v0.2.0/v0.2.1 frontend compatibility)
        ['name' => 'member#index', 'url' => '/api/members', 'verb' => 'GET'],
        ['name' => 'member#create', 'url' => '/api/members', 'verb' => 'POST'],
        ['name' => 'member#show', 'url' => '/api/members/{id}', 'verb' => 'GET'],
        ['name' => 'member#update', 'url' => '/api/members/{id}', 'verb' => 'PUT'],
        ['name' => 'member#destroy', 'url' => '/api/members/{id}', 'verb' => 'DELETE'],

        ['name' => 'finance#index', 'url' => '/api/finance', 'verb' => 'GET'],
        ['name' => 'finance#create', 'url' => '/api/finance', 'verb' => 'POST'],
        ['name' => 'finance#update', 'url' => '/api/finance/{id}', 'verb' => 'PUT'],
        ['name' => 'finance#destroy', 'url' => '/api/finance/{id}', 'verb' => 'DELETE'],

        // Reminders API (v0.2.2)
        ['name' => 'reminderApi#getConfig', 'url' => '/api/v1/reminders/config', 'verb' => 'GET'],
        ['name' => 'reminderApi#saveConfig', 'url' => '/api/v1/reminders/config', 'verb' => 'POST'],
        ['name' => 'reminderApi#getLog', 'url' => '/api/v1/reminders/log', 'verb' => 'GET'],
        ['name' => 'reminderApi#processDue', 'url' => '/api/v1/reminders/process', 'verb' => 'POST'],

        // Privacy/GDPR API (v0.2.2)
        ['name' => 'privacyApi#getPolicy', 'url' => '/api/v1/privacy/policy', 'verb' => 'GET'],
        ['name' => 'privacyApi#exportData', 'url' => '/api/v1/privacy/export/{memberId}', 'verb' => 'GET'],
        ['name' => 'privacyApi#deleteData', 'url' => '/api/v1/privacy/member/{memberId}', 'verb' => 'DELETE'],
        ['name' => 'privacyApi#saveConsent', 'url' => '/api/v1/privacy/consent', 'verb' => 'POST'],
        ['name' => 'privacyApi#getConsent', 'url' => '/api/v1/privacy/consent/{memberId}', 'verb' => 'GET'],

        // Roles API (v0.2.2)
        ['name' => 'rolesApi#getRoles', 'url' => '/api/v1/roles', 'verb' => 'GET'],
        ['name' => 'rolesApi#createRole', 'url' => '/api/v1/roles', 'verb' => 'POST'],
        ['name' => 'rolesApi#updateRole', 'url' => '/api/v1/roles/{roleId}', 'verb' => 'PUT'],
        ['name' => 'rolesApi#deleteRole', 'url' => '/api/v1/roles/{roleId}', 'verb' => 'DELETE'],
        ['name' => 'rolesApi#updatePermissions', 'url' => '/api/v1/roles/{roleId}/permissions', 'verb' => 'PUT'],
    ]
];
