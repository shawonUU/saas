<?php

namespace App\Services\Public\Business\Admin\Sales\Settings\PosSettings;

use App\Models\Business;
use App\Models\Public\Business\Admin\Sales\Settings\PosSettings\RestaurantPosSetting;
use Illuminate\Support\Facades\DB;

class PosSettingsService
{
    /** @return array{kitchen_mode: string, auto_send_to_kitchen: bool, default_service: string} */
    public function forBusiness(Business $business): array
    {
        $settings = RestaurantPosSetting::where('business_id', $business->id)->first();

        return $this->normalize($settings?->only(['kitchen_mode', 'auto_send_to_kitchen', 'default_service']) ?? []);
    }

    public function save(Business $business, array $values): RestaurantPosSetting
    {
        return DB::transaction(function () use ($business, $values): RestaurantPosSetting {
            Business::whereKey($business->id)->lockForUpdate()->firstOrFail();
            $settings = RestaurantPosSetting::firstOrNew(['business_id' => $business->id]);
            if (! $settings->exists) {
                $settings->code = 'RESTAURANT-POS-SETTINGS-'.$business->id;
                $settings->approval_status = 'Approved';
            }
            $settings->fill($this->normalize($values))->save();

            return $settings;
        }, 3);
    }

    /** @return array{kitchen_mode: string, auto_send_to_kitchen: bool, default_service: string} */
    private function normalize(array $settings): array
    {
        $mode = in_array($settings['kitchen_mode'] ?? null, ['off', 'optional', 'required'], true)
            ? $settings['kitchen_mode'] : 'optional';

        return [
            'kitchen_mode' => $mode,
            'auto_send_to_kitchen' => $mode !== 'off' && in_array($settings['auto_send_to_kitchen'] ?? false, [true, 1, '1'], true),
            'default_service' => in_array($settings['default_service'] ?? null, ['takeaway', 'dine_in', 'delivery'], true)
                ? $settings['default_service'] : 'takeaway',
        ];
    }
}
