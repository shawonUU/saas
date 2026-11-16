<?php

namespace App\Http\Controllers\Public\Business\Admin\Configurations;

use App\Models\Configurations\PaymentMethod;

/** Standalone Payment Methods CRUD controller; customize validation here when needed. */
class PaymentMethodController extends BaseConfigurationController
{
    protected const MODEL = PaymentMethod::class;
    protected const SLUG = 'payment-methods';
    protected const TITLE = 'Payment Methods';
    protected const TITLE_BN = 'পেমেন্ট পদ্ধতি';
    protected const FIELDS = [
        'name' => [
            'label' => 'Method Name',
            'type' => 'text',
            'required' => true,
            'options' => [

            ],
        ],
        'method_type' => [
            'label' => 'Method Type',
            'type' => 'select',
            'required' => true,
            'options' => [
                'cash' => 'Cash',
                'card' => 'Card',
                'bank' => 'Bank',
                'mobile' => 'Mobile Banking',
                'other' => 'Other',
            ],
        ],
        'details' => [
            'label' => 'Details',
            'type' => 'textarea',
            'required' => false,
            'options' => [

            ],
        ],
    ];
}
