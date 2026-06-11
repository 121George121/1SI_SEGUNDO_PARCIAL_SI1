<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notificacion', function (Blueprint $table) {
            $table->increments('Id_notificacion');
            $table->string('tipo_notificacion', 50);
            $table->string('titulo', 150);
            $table->text('mensaje');
            $table->string('destinatario', 150);
            $table->string('correo_destinatario', 150);
            $table->date('fecha_envio')->default(DB::raw('CURRENT_DATE'));
            $table->time('hora_envio')->default(DB::raw('CURRENT_TIME'));
            $table->string('estado_envio', 20);
        });

        DB::statement("ALTER TABLE notificacion ADD CONSTRAINT chk_notificacion_estado CHECK (estado_envio IN ('enviado', 'fallido', 'pendiente'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notificacion');
    }
};
