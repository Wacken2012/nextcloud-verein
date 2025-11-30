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

        // App settings
        ['name' => 'settings#getAppSettings', 'url' => '/settings/app', 'verb' => 'GET'],
        ['name' => 'settings#setChartsEnabled', 'url' => '/settings/charts', 'verb' => 'POST'],

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
    ]
];
