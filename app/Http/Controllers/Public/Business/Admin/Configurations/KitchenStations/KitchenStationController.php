<?php

namespace App\Http\Controllers\Public\Business\Admin\Configurations\KitchenStations;

use App\Http\Controllers\Public\Business\Admin\Configurations\RestaurantMasterController;
use App\Models\Configurations\KitchenStation;
use App\Models\Configurations\Product;
use App\Models\Sales\OrderItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class KitchenStationController extends RestaurantMasterController
{
    protected const SLUG = 'kitchen-stations';

    protected const TITLE = 'Kitchen Stations';

    protected const TITLE_BN = 'কিচেন স্টেশন';

    protected const MODEL = KitchenStation::class;

    protected const FIELDS = [
        'name' => ['label' => 'Name', 'label_bn' => 'নাম', 'type' => 'text', 'required' => true, 'options' => []],
        'name_bn' => ['label' => 'Bangla Name', 'label_bn' => 'বাংলা নাম', 'type' => 'text', 'required' => false, 'options' => []],
        'description' => ['label' => 'Description', 'label_bn' => 'বিবরণ', 'type' => 'textarea', 'required' => false, 'options' => []],
    ];

    protected function beforeDelete(Model $row): void
    {
        $this->ensureUnused($row);
    }

    protected function beforeUpdate(Model $row, array $data): void
    {
        if (! $data['is_active']) {
            $this->ensureUnused($row);
        }
    }

    private function ensureUnused(Model $row): void
    {
        $products = Product::where('business_id', $row->business_id)->where('kitchen_station_id', $row->id)->where('is_active', true)->exists();
        $tickets = OrderItem::where('business_id', $row->business_id)->where('kitchen_station_id', $row->id)
            ->whereIn('kitchen_status', ['queued', 'preparing', 'ready'])->exists();
        if ($products || $tickets) {
            throw ValidationException::withMessages(['name' => t('Move the active products and finish pending kitchen tickets before disabling this station.', 'এই স্টেশন বন্ধ করার আগে সক্রিয় পণ্য অন্য স্টেশনে দিন এবং অপেক্ষমাণ কিচেন টিকিট শেষ করুন।')]);
        }
    }
}
