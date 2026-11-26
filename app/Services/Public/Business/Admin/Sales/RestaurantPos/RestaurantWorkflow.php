<?php

namespace App\Services\Public\Business\Admin\Sales\RestaurantPos;

use App\Models\Business;
use App\Models\Sales\Order;
use App\Services\Public\Business\Admin\Sales\Settings\PosSettings\PosSettingsService;

class RestaurantWorkflow
{
    public function __construct(private PosSettingsService $posSettings) {}

    /** @return array{kitchen_mode: string, auto_send_to_kitchen: bool, default_service: string} */
    public function settings(Business $business): array
    {
        return $this->posSettings->forBusiness($business);
    }

    /** @return array{version: int, kitchen_mode: string, auto_send_to_kitchen: bool, table_release: string} */
    public function snapshot(Business $business): array
    {
        $settings = $this->settings($business);

        return [
            'version' => 2,
            'kitchen_mode' => $settings['kitchen_mode'],
            'auto_send_to_kitchen' => $settings['auto_send_to_kitchen'],
            'table_release' => 'manual',
        ];
    }

    /** @return array{version: int, kitchen_mode: string, auto_send_to_kitchen: bool, table_release: string} */
    public function forOrder(Order $order): array
    {
        $policy = $order->restaurant_workflow;
        if (! is_array($policy) || ($policy['version'] ?? null) !== 2) {
            return ['version' => 1, 'kitchen_mode' => 'required', 'auto_send_to_kitchen' => true, 'table_release' => 'automatic'];
        }
        $mode = in_array($policy['kitchen_mode'] ?? null, ['off', 'optional', 'required'], true)
            ? $policy['kitchen_mode'] : 'required';

        return [
            'version' => 2,
            'kitchen_mode' => $mode,
            'auto_send_to_kitchen' => $mode !== 'off' && in_array($policy['auto_send_to_kitchen'] ?? false, [true, 1, '1'], true),
            'table_release' => 'manual',
        ];
    }
}
