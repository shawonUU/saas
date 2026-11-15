<?php
namespace Database\Seeders;
use App\Models\AuthorityUser;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class AuthorityUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = strtolower(trim((string)env('AUTHORITY_SEED_EMAIL', '')));
        $password = (string) env('AUTHORITY_SEED_PASSWORD', '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 12) {
            throw new \RuntimeException('Set AUTHORITY_SEED_EMAIL and a unique AUTHORITY_SEED_PASSWORD (12+ characters) in .env.');
        }
        DB::transaction(function () use ($email, $password) {
            if (AuthorityUser::where('email', $email)->exists()) return;
            if (User::where('email', $email)->exists()) throw new \RuntimeException('Global email already registered; verify identity before linking authority.');
            $global = User::create(['name' => 'Super Admin', 'email' => $email, 'approval_status' => 'Approved']);
            AuthorityUser::create([
                'user_id' => $global->id, 'name' => 'Super Admin', 'email' => $email,
                'password' => $password, 'email_verified_at' => now(),
                'approval_status' => 'Approved', 'is_active' => true, 'approved_at' => now(),
            ]);
        });
    }
}
