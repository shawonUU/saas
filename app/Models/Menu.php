<?php
namespace App\Models;
use App\Models\Concerns\ErpCrud;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Menu extends Model
{
    use ErpCrud, SoftDeletes;
    protected $table = 'menus';
    protected $guarded = [];
    public function module() { return $this->belongsTo(Module::class); }
    public function children() { return $this->hasMany(ChildMenu::class, 'menu_id')->orderBy('sort_order'); }
}
