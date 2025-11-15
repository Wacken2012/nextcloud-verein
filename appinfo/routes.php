<?php
return [
    'routes' => [
        // Dashboard page
        ['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],

        // Dashboard data endpoints
        ['name' => 'member#index', 'url' => '/members', 'verb' => 'GET'],
        ['name' => 'member#create', 'url' => '/members', 'verb' => 'POST'],
        ['name' => 'member#update', 'url' => '/members/{id}', 'verb' => 'PUT'],
        ['name' => 'member#destroy', 'url' => '/members/{id}', 'verb' => 'DELETE'],
        
        ['name' => 'finance#index', 'url' => '/finance', 'verb' => 'GET'],
        ['name' => 'finance#create', 'url' => '/finance', 'verb' => 'POST'],
        ['name' => 'finance#update', 'url' => '/finance/{id}', 'verb' => 'PUT'],
        ['name' => 'finance#destroy', 'url' => '/finance/{id}', 'verb' => 'DELETE'],
        
        ['name' => 'sepa#export', 'url' => '/sepa/export', 'verb' => 'GET'],
    ]
];
