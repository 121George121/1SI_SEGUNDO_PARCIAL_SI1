<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $persona = DB::table('persona')->where('ci', '12345678')->first();

        if (!$persona) {
            $personaId = DB::table('persona')->insertGetId([
                'ci' => '12345678',
                'nombre' => 'Jorge',
                'apellido' => 'Alanoca',
                'sexo' => 'M',
                'fecha_nacimiento' => '2005-01-01',
                'telefono' => '70000000',
                'correo' => 'jorgealanoca2005@gmail.com',
                'direccion' => 'Bolivia',
                'estado' => 'activo',
                'tipo_Superadministrador' => false,
                'tipo_Administrador' => true,
                'tipo_Docente' => false,
                'tipo_Postulante' => false,
            ], 'Id_persona');
        } else {
            $personaId = $persona->Id_persona;
        }

        DB::table('usuario')->updateOrInsert(
            ['correo' => 'jorgealanoca2005@gmail.com'],
            [
            'nombre_usuario' => 'Jorge',
            'correo' => 'jorgealanoca2005@gmail.com',
            'contrasena' => Hash::make('Jorge2005!'),
            'estado' => 'activo',
            'fecha_creacion' => now()->toDateString(),
            'Id_persona' => $personaId,
            ]
        );
    }
}
