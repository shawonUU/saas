<?php

namespace App\Http\Controllers\Public\Business\Admin\Configurations;

use App\Models\Configurations\Brand;

/** Standalone Brands CRUD controller; customize validation here when needed. */
class BrandController extends BaseConfigurationController
{
    protected const MODEL = Brand::class;
    protected const SLUG = 'brands';
    protected const TITLE = 'Brands';
    protected const TITLE_BN = 'ব্র্যান্ড';
    protected const FIELDS = [
        'name' => [
            'label' => 'Brand Name',
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
