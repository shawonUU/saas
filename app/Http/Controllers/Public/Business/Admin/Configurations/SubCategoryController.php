<?php

namespace App\Http\Controllers\Public\Business\Admin\Configurations;

use App\Models\Configurations\SubCategory;

/** Standalone Sub-Categories CRUD controller; customize validation here when needed. */
class SubCategoryController extends BaseConfigurationController
{
    protected const MODEL = SubCategory::class;
    protected const SLUG = 'sub-categories';
    protected const TITLE = 'Sub-Categories';
    protected const TITLE_BN = 'সাব-ক্যাটাগরি';
    protected const FIELDS = [
        'name' => [
            'label' => 'Sub-Category Name',
            'type' => 'text',
            'required' => true,
            'options' => [

            ],
        ],
        'category_id' => [
            'label' => 'Parent Category',
            'type' => 'category',
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

    protected function categories(\App\Models\Business $business)
    {
        return \App\Models\Configurations\Category::query()
            ->where('business_id', $business->id)->where('is_active', true)
            ->orderBy('name')->get(['id','name']);
    }
}
