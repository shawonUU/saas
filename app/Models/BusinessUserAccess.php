<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ErpCrud;
use Illuminate\Database\Eloquent\SoftDeletes;
class BusinessUserAccess extends Model
{
    use ErpCrud, SoftDeletes;
    protected $guarded = [];
    public function user() { return $this->belongsTo(BusinessUser::class, 'business_user_id'); }
    public function module() { return $this->belongsTo(Module::class); }
    public function menu() { return $this->belongsTo(Menu::class); }
    public function childMenu() { return $this->belongsTo(ChildMenu::class); }
}
