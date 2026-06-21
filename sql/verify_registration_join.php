<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Fournisseur;
use App\Models\User;
use Illuminate\Support\Facades\DB;

$email = 'reg-test-' . time() . '@test.local';

DB::beginTransaction();
$user = User::create([
    'nom' => 'Reg', 'prenom' => 'Test', 'email' => $email,
    'password' => bcrypt('password123'), 'role' => 'fournisseur', 'statut' => 'actif',
]);
Fournisseur::create([
    'user_id' => $user->id, 'nom' => 'Ma Societe', 'type' => 'local',
    'statut' => 'inactif', 'email' => $email, 'remise_cooperative' => 0,
]);
DB::commit();

$row = DB::selectOne("
    SELECT u.id, u.email, u.role, f.id AS fournisseur_id, f.nom, f.statut
    FROM users u
    LEFT JOIN fournisseurs f ON f.user_id = u.id
    WHERE u.email = ?
", [$email]);

print_r($row);

// cleanup
Fournisseur::where('user_id', $user->id)->delete();
User::where('id', $user->id)->delete();
echo "Cleaned up test data.\n";
