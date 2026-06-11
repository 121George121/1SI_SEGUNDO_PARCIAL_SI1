<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Crear la función del trigger para sobreescribir NEW.tipo
        DB::unprepared("
            CREATE OR REPLACE FUNCTION trg_before_insert_bitacora()
            RETURNS TRIGGER AS $$
            BEGIN
                NEW.tipo := COALESCE(
                    NULLIF(current_setting('myapp.request_method', true), ''),
                    'LOG'
                );
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
        ");

        // 2. Crear el trigger BEFORE INSERT
        DB::unprepared("
            CREATE TRIGGER trg_before_insert_bitacora_trg
            BEFORE INSERT ON bitacora
            FOR EACH ROW
            EXECUTE FUNCTION trg_before_insert_bitacora();
        ");

        // 3. Mapear los registros existentes a 'LOG'
        DB::statement("
            UPDATE bitacora
            SET tipo = 'LOG'
            WHERE tipo IN ('Documentos', 'Gestion Academica', 'Inscripcion', 'Autenticacion', 'Logistica y Recursos', 'Gestion Financiera', 'Usuarios y Roles')
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP TRIGGER IF EXISTS trg_before_insert_bitacora_trg ON bitacora;");
        DB::unprepared("DROP FUNCTION IF EXISTS trg_before_insert_bitacora();");
    }
};
