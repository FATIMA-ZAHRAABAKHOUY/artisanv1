<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$email = 'test-fourn-' . time() . '@test.local';

try {
    $id = DB::table('users')->insertGetId([
        'nom'        => 'Test',
        'prenom'     => 'Fourn',
        'email'      => $email,
        'password'   => bcrypt('password'),
        'role'       => 'fournisseur',
        'statut'     => 'actif',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    echo "Insert OK, user id={$id}\n";
    DB::table('users')->where('id', $id)->delete();
    echo "Test user deleted.\n";
} catch (Throwable $e) {
    echo "FAIL: " . $e->getMessage() . "\n";
    exit(1);
}
