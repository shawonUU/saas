<?php

namespace App\Http\Controllers\Public\Business\Admin\Configurations;

use App\Models\Configurations\Unit;

/** Standalone Units CRUD controller; customize validation here when needed. */
class UnitController extends BaseConfigurationController
{
    protected const MODEL = Unit::class;
    protected const SLUG = 'units';
    protected const TITLE = 'Units';
    protected const TITLE_BN = 'ইউনিট';
    protected const FIELDS = [
        'name' => [
            'label' => 'Unit Name',
            'type' => 'text',
            'required' => true,
            'options' => [

            ],
        ],
        'symbol' => [
            'label' => 'Symbol',
            'type' => 'text',
            'required' => true,
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
