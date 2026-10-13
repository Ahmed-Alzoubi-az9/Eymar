<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateStaffAccount extends Command
{
    protected $signature = 'staff:create {name} {email}';

    protected $description = 'Create a staff-only Eymar administrator account';

    public function handle(): int
    {
        $email = strtolower((string) $this->argument('email'));

        if (User::query()->where('email', $email)->exists()) {
            $this->error('An account with this email already exists. No account was changed.');

            return self::FAILURE;
        }

        $password = $this->secret('Choose a password (at least 12 characters)');
        if (! is_string($password) || strlen($password) < 12) {
            $this->error('The password must contain at least 12 characters.');

            return self::FAILURE;
        }

        $baseUsername = Str::slug((string) $this->argument('name')) ?: 'administrator';
        $username = Str::limit($baseUsername, 24, '');
        $suffix = 1;
        while (User::query()->where('username', $username)->exists()) {
            $username = Str::limit($baseUsername, 24, '').'-'.$suffix++;
        }

        User::query()->create([
            'name' => (string) $this->argument('name'),
            'email' => $email,
            'username' => $username,
            'password' => Hash::make($password),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->info('Staff account created.');

        return self::SUCCESS;
    }
}
