<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class BusinessUserAccess extends Model
{
    protected $guarded = [];
    public function user() { return $this->belongsTo(BusinessUser::class, 'business_user_id'); }
    public function module() { return $this->belongsTo(Module::class); }
    public function menu() { return $this->belongsTo(Menu::class); }
    public function childMenu() { return $this->belongsTo(ChildMenu::class); }
}
