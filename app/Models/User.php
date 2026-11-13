<?php
namespace App\Models;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\ErpCrud;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
class User extends Authenticatable
{
    use HasFactory, Notifiable, ErpCrud, SoftDeletes;
    protected $guarded = [];
    protected $hidden = ['password', 'remember_token'];
    protected function casts(): array { return ['password' => 'hashed', 'email_verified_at' => 'datetime']; }
    public function businesss() { return $this->hasMany(Business::class, 'owner_id'); }
}
