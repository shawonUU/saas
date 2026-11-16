<?php

namespace App\Http\Controllers\Public\Business\Admin\Configurations;

use App\Models\Configurations\Size;

/** Standalone Sizes CRUD controller; customize validation here when needed. */
class SizeController extends BaseConfigurationController
{
    protected const MODEL = Size::class;
    protected const SLUG = 'sizes';
    protected const TITLE = 'Sizes';
    protected const TITLE_BN = 'সাইজ';
    protected const FIELDS = [
        'name' => [
            'label' => 'Size Name',
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
