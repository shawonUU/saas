<?php
namespace Tests\Feature;
use App\Models\Business;
use App\Models\Configurations\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;
class ErpCommonColumnsTest extends TestCase {
    use RefreshDatabase;
    public function test_common_columns_are_present_on_business_tables(): void {
        foreach (['products','variants','orders','order_items','accounts','journals','journal_lines','payments','warehouses','audit_logs','business_brand_settings'] as $table) {
            $this->assertTrue(Schema::hasColumns($table, ['code','created_by','updated_by','deleted_by','created_ip','updated_ip','deleted_ip','created_user_agent','updated_user_agent','is_active','approval_status','approved_by','approved_at','business_date','created_at','updated_at','deleted_at']), $table);
        }
    }
    public function test_code_generator_skips_legacy_codes_and_soft_deleted_records_are_counted(): void {
        $first = User::create(['name'=>'First','email'=>'first@example.test','code'=>'USR-1']);
        $first->delete();
        User::create(['name'=>'Legacy','email'=>'legacy@example.test','code'=>'EOLDABC']);
        $this->assertSame('USR-2', getGenerateCode(User::class,'code','USR'));
        $next = User::create(['name'=>'Next','email'=>'next@example.test']);
        $this->assertSame('USR-2', $next->code);
        $this->assertSame('USR-3', getGenerateCode(User::class,'code','USR'));
    }
}
