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
        // 1. Crear la función del trigger
        DB::unprepared('
            CREATE OR REPLACE FUNCTION trg_insert_detalle_bitacora()
            RETURNS TRIGGER AS $$
            BEGIN
                INSERT INTO detalle_bitacora ("direccion_ip", "hora_inicio", "hora_fin", "accion", "Id_bitacora")
                VALUES (
                    \'127.0.0.1\',
                    NEW."hora",
                    NEW."hora",
                    COALESCE(NEW."descripcion", \'Acción realizada\'),
                    NEW."Id_bitacora"
                );
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
        ');

        // 2. Crear el disparador en la tabla bitacora
        DB::unprepared('
            CREATE TRIGGER trg_after_insert_bitacora
            AFTER INSERT ON bitacora
            FOR EACH ROW
            EXECUTE FUNCTION trg_insert_detalle_bitacora();
        ');

        // 3. Poblar los registros existentes de bitacora en detalle_bitacora
        DB::statement('
            INSERT INTO detalle_bitacora ("direccion_ip", "hora_inicio", "hora_fin", "accion", "Id_bitacora")
            SELECT \'127.0.0.1\', "hora", "hora", COALESCE("descripcion", \'Acción realizada\'), "Id_bitacora"
            FROM bitacora b
            WHERE NOT EXISTS (
                SELECT 1 FROM detalle_bitacora d WHERE d."Id_bitacora" = b."Id_bitacora"
            )
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_after_insert_bitacora ON bitacora;');
        DB::unprepared('DROP FUNCTION IF EXISTS trg_insert_detalle_bitacora();');
        DB::statement('DELETE FROM detalle_bitacora WHERE "direccion_ip" = \'127.0.0.1\';');
    }
};
