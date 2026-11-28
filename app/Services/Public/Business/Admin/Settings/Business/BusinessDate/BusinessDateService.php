<?php

namespace App\Services\Public\Business\Admin\Settings\Business\BusinessDate;

use App\Models\Business;
use App\Models\Public\Business\Admin\Settings\Business\BusinessDate\BusinessDayClosure;
use App\Models\Public\Business\Admin\Settings\Business\BusinessDate\BusinessDaySetting;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BusinessDateService
{
    public function setting(Business $business): BusinessDaySetting
    {
        $existing = BusinessDaySetting::where('business_id', $business->id)->first();
        if ($existing) return $existing;
        $today = CarbonImmutable::now('Asia/Dhaka')->toDateString();
        DB::table('business_day_settings')->insertOrIgnore([
            'business_id' => $business->id, 'open_date' => $today,
            'manual_mode' => 0, 'timezone' => 'Asia/Dhaka',
            'alert_start_time' => '02:00:00', 'closing_deadline' => '04:00:00',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        return BusinessDaySetting::where('business_id', $business->id)->firstOrFail();
    }

    private function timezone(BusinessDaySetting $setting): string
    {
        $timezone = (string) ($setting->timezone ?: 'Asia/Dhaka');
        return in_array($timezone, timezone_identifiers_list(), true) ? $timezone : 'Asia/Dhaka';
    }

    public function today(BusinessDaySetting $setting): string
    {
        return CarbonImmutable::now($this->timezone($setting))->toDateString();
    }

    public function current(Business $business): BusinessDaySetting
    {
        $state = $this->setting($business);
        if (! $state->manual_mode) {
            $today = $this->today($state);
            if ($state->open_date->toDateString() !== $today) {
                // Auto mode follows the local calendar. Never rewrite previous rows.
                BusinessDaySetting::whereKey($state->id)->where('manual_mode', false)
                    ->where('open_date', '!=', $today)
                    ->update(['open_date' => $today, 'updated_at' => now()]);
                $state->refresh();
            }
        }
        return $state;
    }

    public function status(BusinessDaySetting $state): array
    {
        $now = CarbonImmutable::now($this->timezone($state));
        $open = CarbonImmutable::parse($state->open_date->toDateString(), $this->timezone($state));
        // Alert/deadline occur on the calendar day AFTER the open business date.
        $next = $open->addDay();
        $alert = CarbonImmutable::parse($next->toDateString().' '.substr((string)$state->alert_start_time, 0, 5), $this->timezone($state));
        $deadline = CarbonImmutable::parse($next->toDateString().' '.substr((string)$state->closing_deadline, 0, 5), $this->timezone($state));
        return [
            'today' => $now->toDateString(), 'business_date' => $state->open_date->toDateString(),
            'can_close' => $state->manual_mode && $now->toDateString() > $state->open_date->toDateString(),
            'alert' => $state->manual_mode && $now->greaterThanOrEqualTo($alert),
            'overdue' => $state->manual_mode && $now->greaterThanOrEqualTo($deadline),
            'alert_at' => $alert->format('d/m/Y h:i A'),
            'deadline_at' => $deadline->format('d/m/Y h:i A'),
            'timezone' => $this->timezone($state),
        ];
    }

    public function updateSettings(Business $business, array $validated): BusinessDaySetting
    {
        return DB::transaction(function () use ($business, $validated) {
            $this->setting($business);
            $state = BusinessDaySetting::where('business_id', $business->id)->lockForUpdate()->firstOrFail();
            if ($state->manual_mode && !($validated['manual_mode'] ?? false)
                && $this->today($state) > $state->open_date->toDateString()) {
                throw ValidationException::withMessages(['manual_mode' => t('Close the pending business day before disabling manual mode.', 'ম্যানুয়াল মোড বন্ধের আগে চলমান বিজনেস দিন বন্ধ করুন।')]);
            }
            $newTimezone = $validated['timezone'];
            $today = CarbonImmutable::now($newTimezone)->toDateString();
            if ($state->manual_mode && ($validated['manual_mode'] ?? false)
                && $newTimezone !== $state->timezone && $state->open_date->toDateString() !== $today) {
                throw ValidationException::withMessages(['timezone' => t('Close the pending business day before changing timezone.', 'টাইমজোন বদলানোর আগে চলমান দিন বন্ধ করুন।')]);
            }
            $state->fill([
                'manual_mode' => (bool) $validated['manual_mode'],
                'timezone' => $newTimezone,
                'alert_start_time' => $validated['alert_start_time'],
                'closing_deadline' => $validated['closing_deadline'],
            ]);
            if (! $state->manual_mode) $state->open_date = $today;
            $state->save();
            return $state;
        });
    }

    public function close(Business $business, int $actorId, ?string $actorName, ?string $notes = null): BusinessDayClosure
    {
        return DB::transaction(function () use ($business, $actorId, $actorName, $notes) {
            $this->setting($business);
            $state = BusinessDaySetting::where('business_id', $business->id)->lockForUpdate()->firstOrFail();
            $status = $this->status($state);
            if (! $state->manual_mode) {
                throw ValidationException::withMessages(['business_date' => t('Enable manual business date before closing.', 'দিন বন্ধ করতে ম্যানুয়াল বিজনেস ডেট চালু করুন।')]);
            }
            if (! $status['can_close']) {
                throw ValidationException::withMessages(['business_date' => t('You cannot close the business date until its calendar day has ended.', 'বিজনেস তারিখের দিন শেষ হওয়ার আগে তা বন্ধ করা যাবে না।')]);
            }
            $old = $state->open_date->toDateString();
            $new = $status['today']; // 28th closed on 30th => next date is 30th, never 29th.
            $closure = BusinessDayClosure::create([
                'business_id' => $business->id, 'closed_business_date' => $old,
                'next_business_date' => $new, 'closed_at' => now(),
                'closed_by' => $actorId, 'closed_by_name' => $actorName,
                'notes' => $notes,
            ]);
            $state->open_date = $new;
            $state->save();
            if (DB::getDriverName() === 'mysql') {
                DB::statement('SET @saas_business_date = ?', [$new]);
            }
            return $closure;
        });
    }
}
