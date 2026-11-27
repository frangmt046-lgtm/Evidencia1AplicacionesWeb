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
            'active' => ['admin.users.*'],
            'roles' => [Role::ADMINISTRATOR],
        ],

        'sales' => [
            'title' => 'Order Management',
            'description' => 'Create, search, view, edit and delete customer orders.',
            'route' => 'admin.orders.index',
            'active' => ['admin.orders.index', 'admin.orders.create', 'admin.orders.show', 'admin.orders.edit'],
            'roles' => [Role::SALES, Role::ADMINISTRATOR],
            'statuses' => null,
        ],

        'deleted' => [
            'title' => 'Deleted Orders',
            'description' => 'Search, edit and restore orders that were logically deleted.',
            'route' => 'admin.orders.deleted',
            'active' => ['admin.orders.deleted'],
            'roles' => [Role::SALES, Role::ADMINISTRATOR],
        ],

        'orders' => [
            'title' => 'All Orders',
            'description' => 'Search every active order and update the fields your department is allowed to change.',
            'route' => 'admin.orders.index',
            'active' => ['admin.orders.index', 'admin.orders.show', 'admin.orders.edit'],
            'roles' => [Role::PURCHASING, Role::WAREHOUSE, Role::ROUTE],
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

    'orders' => [

        'manage' => [Role::SALES, Role::ADMINISTRATOR],

        'editable_fields' => [
            Role::ADMINISTRATOR => ['customer', 'ordered_at', 'delivery_address', 'notes', 'status'],
            Role::SALES => ['customer', 'ordered_at', 'delivery_address', 'notes'],
            Role::PURCHASING => ['notes'],
            Role::WAREHOUSE => ['status'],
            Role::ROUTE => ['status'],
        ],

        'status_targets' => [
            Role::ADMINISTRATOR => ['In process', 'In route', 'Delivered'],
            Role::WAREHOUSE => ['In process', 'In route'],
            Role::ROUTE => ['Delivered'],
        ],
    ],

];
