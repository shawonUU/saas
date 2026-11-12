<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
class AuthorityUser extends Authenticatable
{
    use Notifiable;
    protected $table = 'authority_users';
    protected $fillable = ['name', 'code', 'email', 'password', 'approval_status'];
    protected $hidden = ['password', 'remember_token'];
    protected function casts(): array { return ['password' => 'hashed', 'email_verified_at' => 'datetime']; }
}
