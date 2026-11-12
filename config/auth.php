<?php
use App\Models\User;
use App\Models\AuthorityUser;
use App\Models\BusinessUser;
use App\Models\ShopCustomer;
return [
    'defaults' => ['guard' => 'web', 'passwords' => 'users'],
    'guards' => [
        'web' => ['driver' => 'session', 'provider' => 'users'],
        'authority' => ['driver' => 'session', 'provider' => 'authority_users'],
        'shop_admin' => ['driver' => 'session', 'provider' => 'business_users'],
        'shop_customer' => ['driver' => 'session', 'provider' => 'shop_customers'],
    ],
    'providers' => [
        'users' => ['driver' => 'eloquent', 'model' => User::class],
        'authority_users' => ['driver' => 'eloquent', 'model' => AuthorityUser::class],
        'business_users' => ['driver' => 'eloquent', 'model' => BusinessUser::class],
        'shop_customers' => ['driver' => 'eloquent', 'model' => ShopCustomer::class],
    ],
    'passwords' => [
        'users' => ['provider' => 'users', 'table' => 'password_reset_tokens', 'expire' => 60, 'throttle' => 60],
    ],
    'password_timeout' => 10800,
];
