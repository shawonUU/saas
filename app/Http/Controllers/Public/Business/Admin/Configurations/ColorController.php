<?php

namespace App\Http\Controllers\Public\Business\Admin\Configurations;

use App\Models\Configurations\Color;

/** Standalone Colors CRUD controller; customize validation here when needed. */
class ColorController extends BaseConfigurationController
{
    protected const MODEL = Color::class;
    protected const SLUG = 'colors';
    protected const TITLE = 'Colors';
    protected const TITLE_BN = 'রঙ';
    protected const FIELDS = [
        'name' => [
            'label' => 'Color Name',
            'type' => 'text',
            'required' => true,
            'options' => [

            ],
        ],
        'hex_code' => [
            'label' => 'Hex Code',
            'type' => 'hex',
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
