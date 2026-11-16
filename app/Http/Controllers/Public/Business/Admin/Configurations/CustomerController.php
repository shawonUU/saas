<?php

namespace App\Http\Controllers\Public\Business\Admin\Configurations;

use App\Models\Configurations\Customer;

/** Standalone Customers CRUD controller; customize validation here when needed. */
class CustomerController extends BaseConfigurationController
{
    protected const MODEL = Customer::class;
    protected const SLUG = 'customers';
    protected const TITLE = 'Customers';
    protected const TITLE_BN = 'কাস্টমার';
    protected const FIELDS = [
        'name' => [
            'label' => 'Customer Name',
            'type' => 'text',
            'required' => true,
            'options' => [

            ],
        ],
        'phone' => [
            'label' => 'Phone',
            'type' => 'phone',
            'required' => false,
            'options' => [

            ],
        ],
        'email' => [
            'label' => 'Email',
            'type' => 'email',
            'required' => false,
            'options' => [

            ],
        ],
        'address' => [
            'label' => 'Address',
            'type' => 'textarea',
            'required' => false,
            'options' => [

            ],
        ],
        'notes' => [
            'label' => 'Notes',
            'type' => 'textarea',
            'required' => false,
            'options' => [

            ],
        ],
    ];
}
