<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('horario', function (Blueprint $table) {
            $table->unsignedInteger('Id_turno')->nullable();
            $table->foreign('Id_turno')
                ->references('Id_turno')
                ->on('turno')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('horario', function (Blueprint $table) {
            $table->dropForeign(['Id_turno']);
            $table->dropColumn('Id_turno');
        });
    }
};
