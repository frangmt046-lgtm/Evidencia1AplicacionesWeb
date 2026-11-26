<?php

use App\Models\Role;

/*
 * Modules of the Administrative Dashboard and the roles that can open each one.
 * The dashboard shows only the modules of the logged-in user's role, and the
 * routes check the same list, so a user cannot open a module by typing its URL.
 *
 * The Administrator can open every module (supervision), but only the
 * Administrator can manage users.
 */
return [

    'modules' => [

        'users' => [
            'title' => 'User Management',
            'description' => 'Register employees, edit them, activate or deactivate them and assign their role.',
            'route' => 'admin.users.index',
            'roles' => [Role::ADMINISTRATOR],
        ],

        'sales' => [
            'title' => 'Customer Orders',
            'description' => 'Create and manage customer orders.',
            'route' => 'modules.show',
            'roles' => [Role::SALES, Role::ADMINISTRATOR],
            'statuses' => null, // every status
        ],

        'purchasing' => [
            'title' => 'Purchasing',
            'description' => 'Support orders that require purchasing missing materials.',
            'route' => 'modules.show',
            'roles' => [Role::PURCHASING, Role::ADMINISTRATOR],
            'statuses' => ['Ordered'],
        ],

        'warehouse' => [
            'title' => 'Warehouse',
            'description' => 'Process and prepare orders.',
            'route' => 'modules.show',
            'roles' => [Role::WAREHOUSE, Role::ADMINISTRATOR],
            'statuses' => ['Ordered', 'In process'],
        ],

        'route' => [
            'title' => 'Routes & Delivery',
            'description' => 'Manage routed orders and delivery evidence.',
            'route' => 'modules.show',
            'roles' => [Role::ROUTE, Role::ADMINISTRATOR],
            'statuses' => ['In route', 'Delivered'],
        ],
    ],

];
