<?php

namespace App\Http\Controllers\Public\Business\Admin\Configurations\Tables;

use App\Http\Controllers\Public\Business\Admin\Configurations\RestaurantMasterController;
use App\Models\Business;
use App\Models\Configurations\DiningArea;
use App\Models\Configurations\DiningTable;
use App\Models\Sales\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DiningTableController extends RestaurantMasterController
{
    protected const SLUG = 'tables';

    protected const TITLE = 'Tables';

    protected const TITLE_BN = 'টেবিল';

    protected const MODEL = DiningTable::class;

    protected const FIELDS = [
        'name' => ['label' => 'Name', 'label_bn' => 'নাম', 'type' => 'text', 'required' => true, 'options' => []],
        'name_bn' => ['label' => 'Bangla Name', 'label_bn' => 'বাংলা নাম', 'type' => 'text', 'required' => false, 'options' => []],
        'description' => ['label' => 'Description', 'label_bn' => 'বিবরণ', 'type' => 'textarea', 'required' => false, 'options' => []],
        'dining_area_id' => ['label' => 'Dining Area', 'label_bn' => 'ডাইনিং এরিয়া', 'type' => 'select', 'required' => true, 'options' => []],
        'capacity' => ['label' => 'Seats', 'label_bn' => 'আসন', 'type' => 'integer', 'required' => true, 'options' => []],
    ];

    protected function fields(Business $tenant): array
    {
        $fields = static::FIELDS;
        $areas = DiningArea::where('business_id', $tenant->id)->orderBy('name')->get();
        $fields['dining_area_id']['options'] = $areas->pluck('name', 'id')->all();
        $fields['dining_area_id']['options_bn'] = $areas->mapWithKeys(fn ($a) => [$a->id => $a->name_bn ?: $a->name])->all();

        return $fields;
    }

    protected function extraRules(Business $tenant): array
    {
        return ['dining_area_id' => ['required', 'integer', Rule::exists('config_dining_areas', 'id')->where(fn ($q) => $q->where('business_id', $tenant->id)->where('is_active', true)->where('approval_status', 'Approved')->whereNull('deleted_at'))], 'capacity' => 'required|integer|min:1|max:1000'];
    }

    protected function beforeDelete(Model $row): void
    {
        $this->ensureAvailable($row);
    }

    protected function beforeUpdate(Model $row, array $data): void
    {
        if (! $data['is_active'] || (int) $row->capacity !== (int) $data['capacity'] || (int) $row->dining_area_id !== (int) $data['dining_area_id']) {
            $this->ensureAvailable($row);
        }
    }

    private function ensureAvailable(Model $row): void
    {
        $open = Order::where('business_id', $row->business_id)->where('channel', 'restaurant')
            ->where('dining_table_id', $row->id)->whereIn('status', ['draft', 'confirmed'])
            ->whereNull('restaurant_closed_at')->exists();
        $reserved = DB::table('restaurant_reservations')->where('business_id', $row->business_id)
            ->where('dining_table_id', $row->id)->whereNull('deleted_at')
            ->whereIn('status', ['confirmed', 'seated'])->where('ends_at', '>', now())->exists();
        if ($open || $reserved) {
            throw ValidationException::withMessages(['name' => t('Close the open bill or move the reservation before changing this table.', 'এই টেবিল পরিবর্তনের আগে খোলা বিল বন্ধ করুন অথবা রিজার্ভেশন সরান।')]);
        }
    }
}
