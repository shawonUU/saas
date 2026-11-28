<?php

namespace Tests\Unit;

use App\Services\Public\Business\Admin\Settings\Audit\ActivityLogs\AuditSchemaService;
use ReflectionMethod;
use Tests\TestCase;

class AuditSchemaPrivacyTest extends TestCase
{
    public function test_database_audit_snapshots_omit_credential_columns(): void
    {
        $sql = (new ReflectionMethod(AuditSchemaService::class, 'jsonRow'))->invoke(new AuditSchemaService, [
            'id', 'name', 'password', 'access_token', 'api_secret', 'remember_token', 'email',
        ], 'NEW');
        $this->assertStringContainsString('`name`', $sql);
        $this->assertStringContainsString('`email`', $sql);
        $this->assertStringNotContainsString('password', $sql);
        $this->assertStringNotContainsString('token', $sql);
        $this->assertStringNotContainsString('secret', $sql);
    }
}
