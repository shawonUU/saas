<?php

namespace App\Providers;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Blueprint::macro('erpCommonColumns', function () {
            $this->string('code')->unique();
            $this->foreignId('created_by')->nullable();
            $this->foreignId('updated_by')->nullable();
            $this->foreignId('deleted_by')->nullable();
            $this->ipAddress('created_ip')->nullable();
            $this->ipAddress('updated_ip')->nullable();
            $this->ipAddress('deleted_ip')->nullable();
            $this->text('created_user_agent')->nullable();
            $this->text('updated_user_agent')->nullable();
            $this->boolean('is_active')->default(true);
            $this->enum('approval_status', approvalStatus())->default('Pending');
            $this->foreignId('approved_by')->nullable();
            $this->timestamp('approved_at')->nullable();
            $this->date('business_date')->nullable();
            $this->timestamps();
            $this->softDeletes();
        });
    }
}
