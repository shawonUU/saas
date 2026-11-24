<?php

namespace App\Providers;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Blueprint::macro('erpCommonColumns', function (array $existing = [], bool $nullableCode = false) {
            $existing = array_merge($existing, array_map(fn ($column) => $column->name, $this->getColumns()));
            $missing = fn ($name) => !in_array($name, $existing, true);
            if ($missing('code')) $this->string('code')->nullable($nullableCode)->unique();
            foreach (['created_by','updated_by','deleted_by','approved_by'] as $name) {
                if ($missing($name)) $this->foreignId($name)->nullable();
            }
            foreach (['created_ip','updated_ip','deleted_ip'] as $name) {
                if ($missing($name)) $this->ipAddress($name)->nullable();
            }
            foreach (['created_user_agent','updated_user_agent'] as $name) {
                if ($missing($name)) $this->text($name)->nullable();
            }
            if ($missing('is_active')) $this->boolean('is_active')->default(true);
            if ($missing('approval_status')) $this->enum('approval_status', approvalStatus())->default('Pending');
            if ($missing('approved_at')) $this->timestamp('approved_at')->nullable();
            if ($missing('business_date')) $this->date('business_date')->nullable();
            foreach (['created_at','updated_at','deleted_at'] as $name) {
                if ($missing($name)) $this->timestamp($name)->nullable();
            }
        });
    }
}
