<?php

namespace Modules\Business\App\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Modules\Business\App\Providers\EventServiceProvider;
use Modules\Business\App\Providers\RouteServiceProvider;
use Nwidart\Modules\Support\ModuleServiceProvider;

class BusinessServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Business';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'business';

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    // protected array $commands = [];

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    /**
     * Define module schedules.
     * 
     * @param $schedule
     */
    // protected function configureSchedules(Schedule $schedule): void
    // {
    //     $schedule->command('inspire')->hourly();
    // }
}
