<?php

use App\Models\AuthorityUser;
use App\Models\SaasUser;
use App\Models\BusinessUser;
use App\Models\BusinessCustomer;

return [
    'defaults' => ['guard' => 'authority', 'passwords' => 'authority_users'],

    'guards' => [
        'authority' => ['driver' => 'session', 'provider' => 'authority_users'],
        'saas_user' => ['driver' => 'session', 'provider' => 'saas_users'],
        'business_admin' => ['driver' => 'session', 'provider' => 'business_users'],
        'business_customer' => ['driver' => 'session', 'provider' => 'business_customers'],
    ],

    'providers' => [
        'authority_users' => ['driver' => 'eloquent', 'model' => AuthorityUser::class],
        'saas_users' => ['driver' => 'eloquent', 'model' => SaasUser::class],
        'business_users' => ['driver' => 'eloquent', 'model' => BusinessUser::class],
        'business_customers' => ['driver' => 'eloquent', 'model' => BusinessCustomer::class],
    ],

    'passwords' => [
        'authority_users' => ['provider' => 'authority_users', 'table' => 'authority_password_reset_tokens', 'expire' => 60, 'throttle' => 60],
        'saas_users' => ['provider' => 'saas_users', 'table' => 'saas_password_reset_tokens', 'expire' => 60, 'throttle' => 60],
        'business_users' => ['provider' => 'business_users', 'table' => 'business_password_reset_tokens', 'expire' => 60, 'throttle' => 60],
        'business_customers' => ['provider' => 'business_customers', 'table' => 'customer_password_reset_tokens', 'expire' => 60, 'throttle' => 60],
    ],

    'password_timeout' => 10800,
];
