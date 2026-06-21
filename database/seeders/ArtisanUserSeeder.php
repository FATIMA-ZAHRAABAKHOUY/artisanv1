<?php

namespace Database\Seeders;

use App\Models\Artisan;
use App\Models\User;
use Illuminate\Database\Seeder;

class ArtisanUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'artisan@tissu-artisanal.ma'],
            [
                'nom'      => 'Benali',
                'prenom'   => 'Fatima',
                'password' => 'Artisan@2024',
                'role'     => 'artisan',
                'statut'   => 'actif',
                'ville'    => 'Fès',
                'telephone'=> '0612345678',
            ]
        );

        Artisan::updateOrCreate(
            ['user_id' => $user->id],
            [
                'specialite'   => 'Broderie Fassi',
                'statut'       => 'actif',
                'is_verified'  => true,
                'note_moyenne' => 4.5,
            ]
        );
    }
}
