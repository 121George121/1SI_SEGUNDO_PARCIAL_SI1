<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatosPruebaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Limpiar tablas en cascada para evitar duplicados
        DB::statement('TRUNCATE TABLE grupo_horario, grupo_materia, grupo, horario, materia, turno, modalidad, gestion RESTART IDENTITY CASCADE');

        // 2. Sembrar Turnos
        DB::table('turno')->insert([
            ['Id_turno' => 1, 'nombre' => 'Mañana', 'estado' => 'activo'],
            ['Id_turno' => 2, 'nombre' => 'Tarde', 'estado' => 'activo'],
            ['Id_turno' => 3, 'nombre' => 'Noche', 'estado' => 'activo'],
        ]);
        DB::statement("SELECT setval(pg_get_serial_sequence('turno', 'Id_turno'), (SELECT MAX(\"Id_turno\") FROM turno))");

        // 3. Sembrar Aulas
        DB::table('aula')->insert([
            ['Id_aula' => 1, 'nro_aula' => 'A-101', 'capacidad' => 40, 'ubicacion' => 'Planta Baja', 'estado' => 'activo'],
            ['Id_aula' => 2, 'nro_aula' => 'A-102', 'capacidad' => 45, 'ubicacion' => 'Primer Piso', 'estado' => 'activo'],
        ]);
        DB::statement("SELECT setval(pg_get_serial_sequence('aula', 'Id_aula'), (SELECT MAX(\"Id_aula\") FROM aula))");

        // 4. Sembrar Modalidades
        DB::table('modalidad')->insert([
            ['Id_modalidad' => 1, 'nombre_modalidad' => 'Presencial', 'estado' => 'activo'],
            ['Id_modalidad' => 2, 'nombre_modalidad' => 'Virtual', 'estado' => 'activo'],
        ]);
        DB::statement("SELECT setval(pg_get_serial_sequence('modalidad', 'Id_modalidad'), (SELECT MAX(\"Id_modalidad\") FROM modalidad))");

        // 5. Sembrar Gestiones
        DB::table('gestion')->insert([
            ['Id_gestion' => 1, 'anio' => 2026, 'periodo' => 'Primer Semestre', 'fecha_inicio' => '2026-02-01', 'fecha_fin' => '2026-06-30', 'estado' => 'activo'],
        ]);
        DB::statement("SELECT setval(pg_get_serial_sequence('gestion', 'Id_gestion'), (SELECT MAX(\"Id_gestion\") FROM gestion))");

        // 6. Sembrar Materias
        DB::table('materia')->insert([
            ['Id_materia' => 1, 'nombre' => 'COM', 'descripcion' => 'Comunicación', 'estado' => 'activo'],
            ['Id_materia' => 2, 'nombre' => 'MAT', 'descripcion' => 'Matemática', 'estado' => 'activo'],
            ['Id_materia' => 3, 'nombre' => 'FIS', 'descripcion' => 'Física', 'estado' => 'activo'],
            ['Id_materia' => 4, 'nombre' => 'ING', 'descripcion' => 'Inglés', 'estado' => 'activo'],
        ]);
        DB::statement("SELECT setval(pg_get_serial_sequence('materia', 'Id_materia'), (SELECT MAX(\"Id_materia\") FROM materia))");

        // 7. Sembrar Grupo T002 (Turno Tarde, Aula A-101, Modalidad Presencial)
        DB::table('grupo')->insert([
            ['Id_grupo' => 1, 'sigla_grupo' => 'T002', 'capacidad_max' => 40, 'estado' => 'activo', 'cant_estudiantes' => 0, 'Id_aula' => 1, 'Id_modalidad' => 1, 'Id_turno' => 2, 'Id_gestion' => 1],
        ]);
        DB::statement("SELECT setval(pg_get_serial_sequence('grupo', 'Id_grupo'), (SELECT MAX(\"Id_grupo\") FROM grupo))");

        // 8. Sembrar Relaciones de Materia con Docentes para el Grupo T002
        // Docentes: 3 = David Choque, 4 = Rosa Chavez, 5 = Ramiro Torres, 6 = Maria López
        DB::table('grupo_materia')->insert([
            ['Id_grupo' => 1, 'Id_materia' => 1, 'Id_docente' => 3],
            ['Id_grupo' => 1, 'Id_materia' => 2, 'Id_docente' => 4],
            ['Id_grupo' => 1, 'Id_materia' => 3, 'Id_docente' => 5],
            ['Id_grupo' => 1, 'Id_materia' => 4, 'Id_docente' => 6],
        ]);

        // 9. Sembrar Horarios Base para el Turno Tarde (Lunes a Sábado)
        $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        $bloques = [
            ['inicio' => '13:00:00', 'fin' => '14:30:00'],
            ['inicio' => '14:30:00', 'fin' => '16:00:00'],
            ['inicio' => '16:00:00', 'fin' => '17:30:00'],
            ['inicio' => '17:30:00', 'fin' => '19:00:00'],
        ];

        $horarioId = 1;
        foreach ($dias as $dia) {
            foreach ($bloques as $b) {
                DB::table('horario')->insert([
                    'Id_horario' => $horarioId,
                    'dia' => $dia,
                    'hora_inicio' => $b['inicio'],
                    'hora_fin' => $b['fin'],
                    'estado' => 'activo',
                    'Id_turno' => 2 // Turno Tarde
                ]);
                $horarioId++;
            }
        }
        DB::statement("SELECT setval(pg_get_serial_sequence('horario', 'Id_horario'), (SELECT MAX(\"Id_horario\") FROM horario))");

        // 10. Asignar las materias a los horarios creados para el Grupo T002 (Coincidiendo con el Ticket)
        // Lunes, Miércoles, Viernes: COM (13:00), MAT (14:30), FIS (16:00)
        // Martes, Jueves, Sábado: COM (13:00), MAT (14:30), FIS (16:00), ING (17:30)
        $asignaciones = [
            // Lunes (Horarios ID: 1, 2, 3)
            ['Id_horario' => 1, 'Id_materia' => 1], // COM
            ['Id_horario' => 2, 'Id_materia' => 2], // MAT
            ['Id_horario' => 3, 'Id_materia' => 3], // FIS
            
            // Martes (Horarios ID: 5, 6, 7, 8)
            ['Id_horario' => 5, 'Id_materia' => 1], // COM
            ['Id_horario' => 6, 'Id_materia' => 2], // MAT
            ['Id_horario' => 7, 'Id_materia' => 3], // FIS
            ['Id_horario' => 8, 'Id_materia' => 4], // ING

            // Miércoles (Horarios ID: 9, 10, 11)
            ['Id_horario' => 9, 'Id_materia' => 1], // COM
            ['Id_horario' => 10, 'Id_materia' => 2], // MAT
            ['Id_horario' => 11, 'Id_materia' => 3], // FIS

            // Jueves (Horarios ID: 13, 14, 15, 16)
            ['Id_horario' => 13, 'Id_materia' => 1], // COM
            ['Id_horario' => 14, 'Id_materia' => 2], // MAT
            ['Id_horario' => 15, 'Id_materia' => 3], // FIS
            ['Id_horario' => 16, 'Id_materia' => 4], // ING

            // Viernes (Horarios ID: 17, 18, 19)
            ['Id_horario' => 17, 'Id_materia' => 1], // COM
            ['Id_horario' => 18, 'Id_materia' => 2], // MAT
            ['Id_horario' => 19, 'Id_materia' => 3], // FIS

            // Sábado (Horarios ID: 21, 22, 23, 24)
            ['Id_horario' => 21, 'Id_materia' => 1], // COM
            ['Id_horario' => 22, 'Id_materia' => 2], // MAT
            ['Id_horario' => 23, 'Id_materia' => 3], // FIS
            ['Id_horario' => 24, 'Id_materia' => 4], // ING
        ];

        foreach ($asignaciones as $asig) {
            DB::table('grupo_horario')->insert([
                'Id_grupo' => 1,
                'Id_horario' => $asig['Id_horario'],
                'Id_materia' => $asig['Id_materia']
            ]);
        }
    }
}
