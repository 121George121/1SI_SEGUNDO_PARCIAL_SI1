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
        // Truncate persona and all dependent tables to avoid duplicates
        DB::statement('TRUNCATE TABLE persona CASCADE');

        $personas = [
            // ADMINISTRADORES
            [
                'Id_persona' => 1,
                'ci' => '5659827',
                'nombre' => 'Juan',
                'apellido' => 'Chavez',
                'sexo' => 'M',
                'fecha_nacimiento' => '1980-01-01',
                'telefono' => '74825315',
                'correo' => 'juan.chavez34@gmail.com',
                'direccion' => 'Bolivia',
                'estado' => 'activo',
                'tipo_Superadministrador' => false,
                'tipo_Administrador' => true,
                'tipo_Docente' => false,
                'tipo_Postulante' => false,
            ],
            [
                'Id_persona' => 2,
                'ci' => '14389163',
                'nombre' => 'Oscar',
                'apellido' => 'merlos',
                'sexo' => 'M',
                'fecha_nacimiento' => '1980-01-01',
                'telefono' => '76735096',
                'correo' => 'arancibiaoscar35@gmail.com',
                'direccion' => 'Bolivia',
                'estado' => 'activo',
                'tipo_Superadministrador' => false,
                'tipo_Administrador' => true,
                'tipo_Docente' => false,
                'tipo_Postulante' => false,
            ],
            // DOCENTES
            [
                'Id_persona' => 3,
                'ci' => '6171712',
                'nombre' => 'David',
                'apellido' => 'Choque',
                'sexo' => 'M',
                'fecha_nacimiento' => '1975-01-01',
                'telefono' => '73343729',
                'correo' => 'doc.david.choque@gmail.com',
                'direccion' => 'Bolivia',
                'estado' => 'activo',
                'tipo_Superadministrador' => false,
                'tipo_Administrador' => false,
                'tipo_Docente' => true,
                'tipo_Postulante' => false,
            ],
            [
                'Id_persona' => 4,
                'ci' => '8025013',
                'nombre' => 'Rosa',
                'apellido' => 'Chavez',
                'sexo' => 'F',
                'fecha_nacimiento' => '1982-01-01',
                'telefono' => '64303533',
                'correo' => 'doc.rosa.chavez@gmail.com',
                'direccion' => 'Bolivia',
                'estado' => 'activo',
                'tipo_Superadministrador' => false,
                'tipo_Administrador' => false,
                'tipo_Docente' => true,
                'tipo_Postulante' => false,
            ],
            [
                'Id_persona' => 5,
                'ci' => '5796053',
                'nombre' => 'Ramiro',
                'apellido' => 'Torres',
                'sexo' => 'M',
                'fecha_nacimiento' => '1975-01-01',
                'telefono' => '75629166',
                'correo' => 'doc.ramiro.torres@gmail.com',
                'direccion' => 'Bolivia',
                'estado' => 'activo',
                'tipo_Superadministrador' => false,
                'tipo_Administrador' => false,
                'tipo_Docente' => true,
                'tipo_Postulante' => false,
            ],
            [
                'Id_persona' => 6,
                'ci' => '6471626',
                'nombre' => 'Maria',
                'apellido' => 'López',
                'sexo' => 'F',
                'fecha_nacimiento' => '1985-01-01',
                'telefono' => '68097921',
                'correo' => 'doc.maria.lópez@gmail.com',
                'direccion' => 'Bolivia',
                'estado' => 'activo',
                'tipo_Superadministrador' => false,
                'tipo_Administrador' => false,
                'tipo_Docente' => true,
                'tipo_Postulante' => false,
            ],
            // POSTULANTES
            [
                'Id_persona' => 7,
                'ci' => '9694251',
                'nombre' => 'Jorge',
                'apellido' => 'alanoca',
                'sexo' => 'M',
                'fecha_nacimiento' => '2005-01-01',
                'telefono' => '72786717',
                'correo' => 'jorgealanoca2005@gmail.com',
                'direccion' => 'Bolivia',
                'estado' => 'activo',
                'tipo_Superadministrador' => false,
                'tipo_Administrador' => false,
                'tipo_Docente' => false,
                'tipo_Postulante' => true,
            ],
            [
                'Id_persona' => 8,
                'ci' => '7537496',
                'nombre' => 'Rosa',
                'apellido' => 'Torres',
                'sexo' => 'F',
                'fecha_nacimiento' => '2000-01-01',
                'telefono' => '79664866',
                'correo' => 'rosatorres689@gmail.com',
                'direccion' => 'Bolivia',
                'estado' => 'activo',
                'tipo_Superadministrador' => false,
                'tipo_Administrador' => false,
                'tipo_Docente' => false,
                'tipo_Postulante' => true,
            ],
            [
                'Id_persona' => 9,
                'ci' => '8736624',
                'nombre' => 'Oscar',
                'apellido' => 'Mamani',
                'sexo' => 'M',
                'fecha_nacimiento' => '2000-01-01',
                'telefono' => '73079389',
                'correo' => 'oscarmamani560@gmail.com',
                'direccion' => 'Bolivia',
                'estado' => 'activo',
                'tipo_Superadministrador' => false,
                'tipo_Administrador' => false,
                'tipo_Docente' => false,
                'tipo_Postulante' => true,
            ],
            [
                'Id_persona' => 10,
                'ci' => '8833475',
                'nombre' => 'Ruth',
                'apellido' => 'Choque',
                'sexo' => 'F',
                'fecha_nacimiento' => '2000-01-01',
                'telefono' => '69238732',
                'correo' => 'ruthchoque306@gmail.com',
                'direccion' => 'Bolivia',
                'estado' => 'activo',
                'tipo_Superadministrador' => false,
                'tipo_Administrador' => false,
                'tipo_Docente' => false,
                'tipo_Postulante' => true,
            ],
            [
                'Id_persona' => 11,
                'ci' => '4868342',
                'nombre' => 'Ramiro',
                'apellido' => 'Torres',
                'sexo' => 'M',
                'fecha_nacimiento' => '2000-01-01',
                'telefono' => '76670752',
                'correo' => 'ramirotorres903@gmail.com',
                'direccion' => 'Bolivia',
                'estado' => 'activo',
                'tipo_Superadministrador' => false,
                'tipo_Administrador' => false,
                'tipo_Docente' => false,
                'tipo_Postulante' => true,
            ],
            [
                'Id_persona' => 12,
                'ci' => '6955990',
                'nombre' => 'Sonia',
                'apellido' => 'Gutiérrez',
                'sexo' => 'F',
                'fecha_nacimiento' => '2000-01-01',
                'telefono' => '69775489',
                'correo' => 'soniagutiérrez412@gmail.com',
                'direccion' => 'Bolivia',
                'estado' => 'activo',
                'tipo_Superadministrador' => false,
                'tipo_Administrador' => false,
                'tipo_Docente' => false,
                'tipo_Postulante' => true,
            ],
            [
                'Id_persona' => 13,
                'ci' => '5418341',
                'nombre' => 'Alejandro',
                'apellido' => 'López',
                'sexo' => 'M',
                'fecha_nacimiento' => '2000-01-01',
                'telefono' => '69582404',
                'correo' => 'alejandrolópez338@gmail.com',
                'direccion' => 'Bolivia',
                'estado' => 'activo',
                'tipo_Superadministrador' => false,
                'tipo_Administrador' => false,
                'tipo_Docente' => false,
                'tipo_Postulante' => true,
            ],
            [
                'Id_persona' => 14,
                'ci' => '8130237',
                'nombre' => 'Maria',
                'apellido' => 'Chavez',
                'sexo' => 'F',
                'fecha_nacimiento' => '2000-01-01',
                'telefono' => '72747588',
                'correo' => 'mariachavez469@gmail.com',
                'direccion' => 'Bolivia',
                'estado' => 'activo',
                'tipo_Superadministrador' => false,
                'tipo_Administrador' => false,
                'tipo_Docente' => false,
                'tipo_Postulante' => true,
            ],
            [
                'Id_persona' => 15,
                'ci' => '7570419',
                'nombre' => 'Jorge',
                'apellido' => 'López',
                'sexo' => 'M',
                'fecha_nacimiento' => '2000-01-01',
                'telefono' => '64990832',
                'correo' => 'jorgelópez569@gmail.com',
                'direccion' => 'Bolivia',
                'estado' => 'activo',
                'tipo_Superadministrador' => false,
                'tipo_Administrador' => false,
                'tipo_Docente' => false,
                'tipo_Postulante' => true,
            ],
            [
                'Id_persona' => 16,
                'ci' => '6885810',
                'nombre' => 'Elizabeth',
                'apellido' => 'Fernández',
                'sexo' => 'F',
                'fecha_nacimiento' => '2000-01-01',
                'telefono' => '72387377',
                'correo' => 'elizabethfernández103@gmail.com',
                'direccion' => 'Bolivia',
                'estado' => 'activo',
                'tipo_Superadministrador' => false,
                'tipo_Administrador' => false,
                'tipo_Docente' => false,
                'tipo_Postulante' => true,
            ],
        ];

        foreach ($personas as $p) {
            DB::table('persona')->insert($p);
        }

        // Insertar en Administrador
        DB::table('administrador')->insert([
            [
                'Id_administrador' => 1,
                'cargo' => 'Coordinador Académico',
                'area' => 'Académica',
                'estado' => 'activo',
            ],
            [
                'Id_administrador' => 2,
                'cargo' => 'Coordinador Académico',
                'area' => 'Académica',
                'estado' => 'activo',
            ],
        ]);

        // Insertar en Docente
        DB::table('docente')->insert([
            [
                'Id_docente' => 3,
                'anio_servicio' => 12,
                'estado' => 'activo',
            ],
            [
                'Id_docente' => 4,
                'anio_servicio' => 4,
                'estado' => 'activo',
            ],
            [
                'Id_docente' => 5,
                'anio_servicio' => 12,
                'estado' => 'activo',
            ],
            [
                'Id_docente' => 6,
                'anio_servicio' => 2,
                'estado' => 'activo',
            ],
        ]);

        // Insertar en Postulante
        DB::table('postulante')->insert([
            [
                'Id_postulante' => 7,
                'estado_inscripcion' => 'Observado',
                'fecha_registro' => now()->toDateString(),
                'Id_asignacioncupo' => null,
            ],
            [
                'Id_postulante' => 8,
                'estado_inscripcion' => 'Pendiente',
                'fecha_registro' => now()->toDateString(),
                'Id_asignacioncupo' => null,
            ],
            [
                'Id_postulante' => 9,
                'estado_inscripcion' => 'En Revisión',
                'fecha_registro' => now()->toDateString(),
                'Id_asignacioncupo' => null,
            ],
            [
                'Id_postulante' => 10,
                'estado_inscripcion' => 'Aceptado',
                'fecha_registro' => now()->toDateString(),
                'Id_asignacioncupo' => null,
            ],
            [
                'Id_postulante' => 11,
                'estado_inscripcion' => 'En Revisión',
                'fecha_registro' => now()->toDateString(),
                'Id_asignacioncupo' => null,
            ],
            [
                'Id_postulante' => 12,
                'estado_inscripcion' => 'Observado',
                'fecha_registro' => now()->toDateString(),
                'Id_asignacioncupo' => null,
            ],
            [
                'Id_postulante' => 13,
                'estado_inscripcion' => 'Observado',
                'fecha_registro' => now()->toDateString(),
                'Id_asignacioncupo' => null,
            ],
            [
                'Id_postulante' => 14,
                'estado_inscripcion' => 'En Revisión',
                'fecha_registro' => now()->toDateString(),
                'Id_asignacioncupo' => null,
            ],
            [
                'Id_postulante' => 15,
                'estado_inscripcion' => 'Pendiente',
                'fecha_registro' => now()->toDateString(),
                'Id_asignacioncupo' => null,
            ],
            [
                'Id_postulante' => 16,
                'estado_inscripcion' => 'Observado',
                'fecha_registro' => now()->toDateString(),
                'Id_asignacioncupo' => null,
            ],
        ]);

        // Insertar en Usuario para permitir el inicio de sesión
        $usuarios = [
            1 => 'juan.chavez',
            2 => 'oscar.merlos',
            3 => 'david.choque',
            4 => 'rosa.chavez',
            5 => 'ramiro.torres.5',
            6 => 'maria.lopez',
            7 => 'jorge.alanoca',
            8 => 'rosa.torres',
            9 => 'oscar.mamani',
            10 => 'ruth.choque',
            11 => 'ramiro.torres.11',
            12 => 'sonia.gutierrez',
            13 => 'alejandro.lopez',
            14 => 'maria.chavez',
            15 => 'jorge.lopez',
            16 => 'elizabeth.fernandez',
        ];

        foreach ($personas as $p) {
            $id = $p['Id_persona'];
            $username = $usuarios[$id];
            
            DB::table('usuario')->insert([
                'nombre_usuario' => $username,
                'correo' => $p['correo'],
                'contrasena' => Hash::make('Password123!'),
                'estado' => 'activo',
                'fecha_creacion' => now()->toDateString(),
                'Id_persona' => $id,
            ]);
        }

        // Sincronizar secuencias de PostgreSQL
        DB::statement("SELECT setval(pg_get_serial_sequence('persona', 'Id_persona'), (SELECT MAX(\"Id_persona\") FROM persona))");
        DB::statement("SELECT setval(pg_get_serial_sequence('usuario', 'Id_usuario'), (SELECT MAX(\"Id_usuario\") FROM usuario))");
    }
}
