<?php

namespace App\Http\Controllers\Public\Business\Admin\Configurations;

use App\Models\Configurations\Supplier;

/** Standalone Suppliers CRUD controller; customize validation here when needed. */
class SupplierController extends BaseConfigurationController
{
    protected const MODEL = Supplier::class;
    protected const SLUG = 'suppliers';
    protected const TITLE = 'Suppliers';
    protected const TITLE_BN = 'সরবরাহকারী';
    protected const FIELDS = [
        'name' => [
            'label' => 'Supplier Name',
            'type' => 'text',
            'required' => true,
            'options' => [

            ],
        ],
        'contact_person' => [
            'label' => 'Contact Person',
            'type' => 'text',
            'required' => false,
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
