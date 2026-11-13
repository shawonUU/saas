<?php
namespace App\Models;
use App\Models\Concerns\ErpCrud;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class ChildMenu extends Model
{
    use ErpCrud, SoftDeletes;
    protected $table = 'child_menus';
    protected $guarded = [];
    public function menu() { return $this->belongsTo(Menu::class); }
}
