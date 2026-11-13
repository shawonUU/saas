<?php
namespace App\Models;
use App\Models\Concerns\ErpCrud;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class BusinessChildMenu extends Model
{
    use ErpCrud, SoftDeletes;
    protected $table = 'business_child_menus';
    protected $guarded = [];
    public function business() { return $this->belongsTo(Business::class); }
    public function childMenu() { return $this->belongsTo(ChildMenu::class); }
}
