<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ShopCategory extends Model
{
    protected $fillable = ['name', 'code', 'approval_status'];
}
