<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Le cast 'hashed' sur User hash automatiquement le mot de passe en Bcrypt.
        User::updateOrCreate(
            ['email' => 'admin@tissu-artisanal.ma'],
            [
                'nom'      => 'Admin',
                'prenom'   => 'Coopérative',
                'password' => 'Admin@2024',
                'role'     => 'admin',
                'statut'   => 'actif',
                'ville'    => 'Fès',
            ]
        );
    }
}
