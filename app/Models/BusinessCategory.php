<?php
namespace App\Models;
use App\Models\Concerns\ErpCrud;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class BusinessCategory extends Model
{
    use ErpCrud, SoftDeletes;
    protected $guarded = [];
    public function businesses() { return $this->hasMany(Business::class, 'category_id'); }
    public function modules() { return $this->belongsToMany(Module::class, 'business_category_modules', 'business_category_id', 'module_id'); }
    public function menus() { return $this->belongsToMany(Menu::class, 'business_category_menus', 'business_category_id', 'menu_id'); }
    public function childMenus() { return $this->belongsToMany(ChildMenu::class, 'business_category_child_menus', 'business_category_id', 'child_menu_id'); }
}
