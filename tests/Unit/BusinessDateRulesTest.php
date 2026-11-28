<?php

namespace Tests\Unit;

use App\Models\Public\Business\Admin\Settings\Business\BusinessDate\BusinessDaySetting;
use App\Services\Public\Business\Admin\Settings\Business\BusinessDate\BusinessDateService;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class BusinessDateRulesTest extends TestCase
{
    public function test_manual_date_cannot_close_before_its_calendar_day_finishes(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-11-28 23:59:00', 'Asia/Dhaka'));
        try {
            $state = new BusinessDaySetting(['manual_mode' => true, 'open_date' => '2026-11-28', 'timezone' => 'Asia/Dhaka', 'alert_start_time' => '02:00:00', 'closing_deadline' => '04:00:00']);
            $status = (new BusinessDateService)->status($state);
            $this->assertFalse($status['can_close']);
            $this->assertFalse($status['alert']);
        } finally {
            CarbonImmutable::setTestNow();
        }
    }

    public function test_manual_day_is_still_open_and_overdue_two_days_later(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-11-30 09:00:00', 'Asia/Dhaka'));
        try {
            $state = new BusinessDaySetting(['manual_mode' => true, 'open_date' => '2026-11-28', 'timezone' => 'Asia/Dhaka', 'alert_start_time' => '02:00:00', 'closing_deadline' => '04:00:00']);
            $status = (new BusinessDateService)->status($state);
            $this->assertSame('2026-11-28', $status['business_date']);
            $this->assertSame('2026-11-30', $status['today']);
            $this->assertTrue($status['can_close']);
            $this->assertTrue($status['alert']);
            $this->assertTrue($status['overdue']);
        } finally {
            CarbonImmutable::setTestNow();
        }
    }
}
