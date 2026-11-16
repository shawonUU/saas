<?php

namespace App\Http\Controllers\Public\Business\Admin\Configurations;

use App\Models\Configurations\ShippingMethod;

/** Standalone Shipping Methods CRUD controller; customize validation here when needed. */
class ShippingMethodController extends BaseConfigurationController
{
    protected const MODEL = ShippingMethod::class;
    protected const SLUG = 'shipping-methods';
    protected const TITLE = 'Shipping Methods';
    protected const TITLE_BN = 'শিপিং পদ্ধতি';
    protected const FIELDS = [
        'name' => [
            'label' => 'Method Name',
            'type' => 'text',
            'required' => true,
            'options' => [

            ],
        ],
        'charge' => [
            'label' => 'Base Charge',
            'type' => 'decimal',
            'required' => true,
            'options' => [

            ],
        ],
        'estimated_days' => [
            'label' => 'Estimated Days',
            'type' => 'integer',
            'required' => false,
            'options' => [

            ],
        ],
        'description' => [
            'label' => 'Description',
            'type' => 'textarea',
            'required' => false,
            'options' => [

            ],
        ],
    ];
}
