<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class RehashPlainPasswordsSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->each(function (User $user) {
            $stored = $user->getRawOriginal('password');

            if ($this->isBcryptHash($stored)) {
                return;
            }

            // Mot de passe en clair en base : le cast 'hashed' le convertit en Bcrypt.
            $user->password = $stored;
            $user->saveQuietly();
        });
    }

    private function isBcryptHash(?string $value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        return str_starts_with($value, '$2y$')
            || str_starts_with($value, '$2a$')
            || str_starts_with($value, '$2b$');
    }
}
