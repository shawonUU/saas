<?php

namespace App\Http\Controllers\Public\Business\Admin\Configurations;

use App\Models\Configurations\Category;

/** Standalone Categories CRUD controller; customize validation here when needed. */
class CategoryController extends BaseConfigurationController
{
    protected const MODEL = Category::class;
    protected const SLUG = 'categories';
    protected const TITLE = 'Categories';
    protected const TITLE_BN = 'ক্যাটাগরি';
    protected const FIELDS = [
        'name' => [
            'label' => 'Category Name',
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

    protected function beforeDelete(\Illuminate\Database\Eloquent\Model $record, \App\Models\Business $business): void
    {
        if (\App\Models\Configurations\SubCategory::query()
            ->where('business_id',$business->id)->where('category_id',$record->id)->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'category'=>'This category has sub-categories. Delete them first.',
            ]);
        }
    }
}
