<?php
namespace App\Models;
use App\Models\Concerns\ErpCrud;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Module extends Model
{
    use ErpCrud, SoftDeletes;
    protected $table = 'modules';
    protected $guarded = [];
    public function menus() { return $this->hasMany(Menu::class)->orderBy('sort_order'); }
}
