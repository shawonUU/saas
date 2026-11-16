<?php

namespace App\Http\Controllers\Public\Business\Admin\Configurations;

use App\Models\Configurations\Tax;

/** Standalone Taxes CRUD controller; customize validation here when needed. */
class TaxController extends BaseConfigurationController
{
    protected const MODEL = Tax::class;
    protected const SLUG = 'taxes';
    protected const TITLE = 'Taxes';
    protected const TITLE_BN = 'ট্যাক্স';
    protected const FIELDS = [
        'name' => [
            'label' => 'Tax Name',
            'type' => 'text',
            'required' => true,
            'options' => [

            ],
        ],
        'rate' => [
            'label' => 'Rate (%)',
            'type' => 'decimal',
            'required' => true,
            'options' => [

            ],
        ],
        'calculation_type' => [
            'label' => 'Calculation',
            'type' => 'select',
            'required' => true,
            'options' => [
                'exclusive' => 'Exclusive',
                'inclusive' => 'Inclusive',
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
