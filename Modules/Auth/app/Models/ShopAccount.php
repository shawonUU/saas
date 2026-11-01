<?php
namespace Modules\Auth\App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class ShopAccount extends Authenticatable
{
    use HasApiTokens;
    protected $table = 'shop_accounts';
    protected $guarded = [];
    protected $hidden = ['password', 'remember_token'];
    protected function casts(): array { return ['password' => 'hashed']; }
}
