<?php
namespace App\Models;
use App\Models\Concerns\ErpCrud;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class BusinessCategoryMenu extends Model
{
    use ErpCrud, SoftDeletes;
    protected $table = 'business_category_menus';
    protected $guarded = [];
    public function category() { return $this->belongsTo(BusinessCategory::class, 'business_category_id'); }
    public function menu() { return $this->belongsTo(Menu::class); }
}
