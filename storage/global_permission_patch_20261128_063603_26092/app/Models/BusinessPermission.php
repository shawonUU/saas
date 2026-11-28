<?php
namespace App\Models;
use App\Models\Concerns\ErpCrud;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class BusinessPermission extends Model
{
    use ErpCrud, SoftDeletes;
    protected $guarded = [];
    protected function casts(): array { return ['is_active'=>'boolean']; }
    public function business() { return $this->belongsTo(Business::class); }
}
