<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Usuario;
use App\Models\AccesoWeb;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Seed the usuarios table
        Usuario::create([
            'usuario' => 'admin',
            'password' => bcrypt('admin123'),
            'tipousuario' => 'admin',
        ]);

        Usuario::create([
            'usuario' => 'vendedor',
            'password' => bcrypt('vendedor123'),
            'tipousuario' => 'vendedor',
        ]);

        // Seed the acceso_web table
        AccesoWeb::create([
            'usuario_id' => 1, // Assuming admin has ID 1
            'acceso' => 'full_access',
        ]);

        AccesoWeb::create([
            'usuario_id' => 2, // Assuming vendedor has ID 2
            'acceso' => 'limited_access',
        ]);
    }
}