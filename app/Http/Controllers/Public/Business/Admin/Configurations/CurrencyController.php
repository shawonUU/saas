<?php

namespace App\Http\Controllers\Public\Business\Admin\Configurations;

use App\Models\Configurations\Currency;

/** Standalone Currencies CRUD controller; customize validation here when needed. */
class CurrencyController extends BaseConfigurationController
{
    protected const MODEL = Currency::class;
    protected const SLUG = 'currencies';
    protected const TITLE = 'Currencies';
    protected const TITLE_BN = 'মুদ্রা';
    protected const FIELDS = [
        'name' => [
            'label' => 'Currency Name',
            'type' => 'text',
            'required' => true,
            'options' => [

            ],
        ],
        'currency_code' => [
            'label' => 'ISO Code (e.g. BDT)',
            'type' => 'currency',
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
        'exchange_rate' => [
            'label' => 'Exchange Rate',
            'type' => 'decimal',
            'required' => true,
            'options' => [

            ],
        ],
        'is_default' => [
            'label' => 'Default',
            'type' => 'boolean',
            'required' => false,
            'options' => [

            ],
        ],
    ];

    protected function prepareInput(\Illuminate\Http\Request $request): void
    {
        $request->merge(['currency_code'=>strtoupper(trim((string)$request->input('currency_code')))]);
    }

    protected function beforeSave(array $data, ?\Illuminate\Database\Eloquent\Model $record, \App\Models\Business $business): void
    {
        if ($data['is_default'] ?? false) {
            \App\Models\Configurations\Currency::query()
                ->where('business_id', $business->id)
                ->when($record, fn($q)=>$q->whereKeyNot($record->id))
                ->update(['is_default'=>false]);
        }
    }
}
