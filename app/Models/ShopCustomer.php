<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
class ShopCustomer extends Authenticatable
{
    use Notifiable;
    protected $table = 'shop_customers';
    protected $fillable = ['shop_id', 'name', 'code', 'email', 'password', 'approval_status'];
    protected $hidden = ['password', 'remember_token'];
    protected function casts(): array { return ['password' => 'hashed', 'email_verified_at' => 'datetime']; }
    public function shop() { return $this->belongsTo(Shop::class); }
}
