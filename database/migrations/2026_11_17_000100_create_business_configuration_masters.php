<?php

use Illuminate\Database\Migrations\Migration;

/** Retired combined migration: individual 12 master migrations now manage new installs.
 *  Keep this filename as a migration history marker for already-migrated deployments.
 *  Do not drop existing legacy master tables on rollback.
 */
return new class extends Migration {
    public function up(): void {}
    public function down(): void {}
};
