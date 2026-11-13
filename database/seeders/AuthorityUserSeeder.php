<?php

namespace Database\Seeders;

use App\Models\AuthorityUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AuthorityUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = strtolower(trim(env('AUTHORITY_SEED_EMAIL', 'admin@saas.test')));
        $password = env('AUTHORITY_SEED_PASSWORD', '12345678');

        if (!$password || strlen($password) < 8) {
            throw new \RuntimeException('Set AUTHORITY_SEED_PASSWORD in .env with at least 12 characters.');
        }

        AuthorityUser::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Super Admin',
                'password' => Hash::make($password),
                'email_verified_at' => now(),
                'approval_status' => 'Approved',
                'is_active' => true,
                'approved_at' => now(),
            ]
        );
    }
}