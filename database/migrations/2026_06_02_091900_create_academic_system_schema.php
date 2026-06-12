<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('persona', function (Blueprint $table) {
            $table->increments('Id_persona');
            $table->string('ci', 20)->unique();
            $table->string('nombre', 100);
            $table->string('apellido', 100);
            $table->char('sexo', 1)->nullable();
            $table->date('fecha_nacimiento');
            $table->string('telefono', 20)->nullable();
            $table->string('correo', 150)->nullable();
            $table->text('direccion')->nullable();
            $table->string('estado', 20)->default('activo');
            $table->boolean('tipo_Superadministrador')->default(false);
            $table->boolean('tipo_Administrador')->default(false);
            $table->boolean('tipo_Docente')->default(false);
            $table->boolean('tipo_Postulante')->default(false);
        });

        Schema::create('carrera', function (Blueprint $table) {
            $table->increments('Id_carrera');
            $table->string('nombre_carrera', 150);
            $table->text('descripcion')->nullable();
            $table->string('estado', 20)->default('activo');
        });

        Schema::create('gestion', function (Blueprint $table) {
            $table->increments('Id_gestion');
            $table->smallInteger('anio');
            $table->string('periodo', 50);
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->string('estado', 20)->default('activo');
        });

        DB::statement('ALTER TABLE gestion ADD CONSTRAINT chk_gestion_anio CHECK (anio >= 2000 AND anio <= 2100)');

        Schema::create('especialidad', function (Blueprint $table) {
            $table->increments('Id_especialidad');
            $table->string('nombre_especialidad', 150);
        });

        Schema::create('aula', function (Blueprint $table) {
            $table->increments('Id_aula');
            $table->string('nro_aula', 50)->unique();
            $table->integer('capacidad')->nullable();
            $table->string('ubicacion', 100)->nullable();
            $table->string('estado', 20)->default('activo');
        });

        Schema::create('modalidad', function (Blueprint $table) {
            $table->increments('Id_modalidad');
            $table->string('nombre_modalidad', 100);
            $table->string('estado', 20)->default('activo');
        });

        Schema::create('turno', function (Blueprint $table) {
            $table->increments('Id_turno');
            $table->string('nombre', 50);
            $table->string('estado', 20)->default('activo');
        });

        Schema::create('horario', function (Blueprint $table) {
            $table->increments('Id_horario');
            $table->string('dia', 20);
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->string('estado', 20)->default('activo');
        });

        Schema::create('materia', function (Blueprint $table) {
            $table->increments('Id_materia');
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable();
            $table->string('estado', 20)->default('activo');
        });

        Schema::create('comprobante', function (Blueprint $table) {
    $table->increments('Id_comprobante');
    $table->string('nro_comprobante', 50);
    $table->date('fecha_emision');
    $table->string('archivo', 255)->nullable(); // <--- Aquí está el nuevo atributo
});

        Schema::create('superadministrador', function (Blueprint $table) {
            $table->unsignedInteger('Id_superadministrador')->primary();
            $table->string('cargo', 100)->nullable();
            $table->string('estado', 20)->default('activo');

            $table->foreign('Id_superadministrador')
                ->references('Id_persona')
                ->on('persona')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        Schema::create('administrador', function (Blueprint $table) {
            $table->unsignedInteger('Id_administrador')->primary();
            $table->string('cargo', 100)->nullable();
            $table->string('area', 100)->nullable();
            $table->string('estado', 20)->default('activo');

            $table->foreign('Id_administrador')
                ->references('Id_persona')
                ->on('persona')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        Schema::create('docente', function (Blueprint $table) {
            $table->unsignedInteger('Id_docente')->primary();
            $table->integer('anio_servicio')->nullable();
            $table->string('estado', 20)->default('activo');

            $table->foreign('Id_docente')
                ->references('Id_persona')
                ->on('persona')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        Schema::create('asignacioncupo', function (Blueprint $table) {
            $table->increments('Id_asignacioncupo');
            $table->date('fecha_asignacion');
            $table->decimal('promedio_final', 5, 2)->nullable();
            $table->integer('puesto_merito')->nullable();
            $table->string('estado_asignacion', 20);
            $table->unsignedInteger('Id_carrera');
            $table->unsignedInteger('Id_gestion');

            $table->foreign('Id_carrera')
                ->references('Id_carrera')
                ->on('carrera')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('Id_gestion')
                ->references('Id_gestion')
                ->on('gestion')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        Schema::create('postulante', function (Blueprint $table) {
            $table->unsignedInteger('Id_postulante')->primary();
            $table->string('estado_inscripcion', 20);
            $table->date('fecha_registro')->default(DB::raw('CURRENT_DATE'));
            $table->unsignedInteger('Id_asignacioncupo')->nullable()->unique();

            $table->foreign('Id_postulante')
                ->references('Id_persona')
                ->on('persona')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('Id_asignacioncupo')
                ->references('Id_asignacioncupo')
                ->on('asignacioncupo')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });

        Schema::create('usuario', function (Blueprint $table) {
            $table->increments('Id_usuario');
            $table->string('nombre_usuario', 50)->unique();
            $table->string('correo', 150)->unique();
            $table->string('contrasena', 255);
            $table->string('estado', 20)->default('activo');
            $table->date('fecha_creacion')->default(DB::raw('CURRENT_DATE'));
            $table->unsignedInteger('Id_persona');

            $table->foreign('Id_persona')
                ->references('Id_persona')
                ->on('persona')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        Schema::create('reporte', function (Blueprint $table) {
            $table->increments('Id_reporte');
            $table->string('tipo_reporte', 50);
            $table->date('fecha_generacion')->default(DB::raw('CURRENT_DATE'));
            $table->text('descripcion')->nullable();
            $table->text('filtro_usado')->nullable();
            $table->unsignedInteger('Id_usuario');

            $table->foreign('Id_usuario')
                ->references('Id_usuario')
                ->on('usuario')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        Schema::create('bitacora', function (Blueprint $table) {
            $table->increments('Id_bitacora');
            $table->string('tipo', 50);
            $table->text('descripcion')->nullable();
            $table->date('fecha')->default(DB::raw('CURRENT_DATE'));
            $table->time('hora')->default(DB::raw('CURRENT_TIME'));
            $table->string('estado', 20);
            $table->unsignedInteger('Id_usuario');

            $table->foreign('Id_usuario')
                ->references('Id_usuario')
                ->on('usuario')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        Schema::create('detalle_bitacora', function (Blueprint $table) {
            $table->increments('Id_detallebitacora');
            $table->string('direccion_ip', 45)->nullable();
            $table->time('hora_inicio')->nullable();
            $table->time('hora_fin')->nullable();
            $table->text('accion')->nullable();
            $table->unsignedInteger('Id_bitacora');

            $table->foreign('Id_bitacora')
                ->references('Id_bitacora')
                ->on('bitacora')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        Schema::create('inscripcion', function (Blueprint $table) {
            $table->increments('Codigo_inscripcion');
            $table->string('estado', 20);
            $table->date('fecha_inscripcion')->default(DB::raw('CURRENT_DATE'));
            $table->unsignedInteger('Id_postulante');
            $table->unsignedInteger('Id_gestion');

            $table->foreign('Id_postulante')
                ->references('Id_postulante')
                ->on('postulante')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('Id_gestion')
                ->references('Id_gestion')
                ->on('gestion')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        Schema::create('inscripcion_carrera', function (Blueprint $table) {
            $table->unsignedInteger('Codigo_inscripcion');
            $table->unsignedInteger('Id_carrera');
            $table->integer('prioridad');
            $table->string('estado', 20);

            $table->primary(['Codigo_inscripcion', 'Id_carrera']);

            $table->foreign('Codigo_inscripcion')
                ->references('Codigo_inscripcion')
                ->on('inscripcion')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('Id_carrera')
                ->references('Id_carrera')
                ->on('carrera')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        Schema::create('preferencia_inscripcion', function (Blueprint $table) {
            $table->increments('Id_preferencia');
            $table->unsignedInteger('Codigo_inscripcion');
            $table->unsignedInteger('Id_modalidad');
            $table->unsignedInteger('Id_turno');
            $table->string('estado', 20)->default('activo');

            $table->foreign('Codigo_inscripcion')
                ->references('Codigo_inscripcion')
                ->on('inscripcion')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('Id_modalidad')
                ->references('Id_modalidad')
                ->on('modalidad')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('Id_turno')
                ->references('Id_turno')
                ->on('turno')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        Schema::create('documento', function (Blueprint $table) {
            $table->increments('Id_documento');
            $table->string('tipo_documento', 50);
            $table->string('nombre', 100);
            $table->date('fecha_registro')->default(DB::raw('CURRENT_DATE'));
            $table->string('destinado_a', 50);
            $table->text('descripcion')->nullable();
        });

        Schema::create('persona_documento', function (Blueprint $table) {
            $table->unsignedInteger('Id_persona');
            $table->unsignedInteger('Id_documento');
            $table->string('estado', 20);
            $table->text('observacion')->nullable();
            $table->date('fecha_revision')->nullable();
            $table->unsignedInteger('Id_administrador')->nullable();

            $table->primary(['Id_persona', 'Id_documento']);

            $table->foreign('Id_persona')
                ->references('Id_persona')
                ->on('persona')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('Id_documento')
                ->references('Id_documento')
                ->on('documento')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('Id_administrador')
                ->references('Id_administrador')
                ->on('administrador')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });


        Schema::create('cupocarrera', function (Blueprint $table) {
            $table->increments('Id_cupo');
            $table->integer('cantidad_cupos');
            $table->unsignedInteger('Id_gestion');
            $table->unsignedInteger('Id_carrera');

            $table->unique(['Id_gestion', 'Id_carrera']);

            $table->foreign('Id_gestion')
                ->references('Id_gestion')
                ->on('gestion')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('Id_carrera')
                ->references('Id_carrera')
                ->on('carrera')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        Schema::create('pago', function (Blueprint $table) {
            $table->increments('Id_pago');
            $table->string('concepto_pago', 50);
            $table->decimal('monto', 10, 2);
            $table->string('estado_pago', 20)->default('activo');

            $table->text('observaciones')->nullable();
        });

        Schema::create('pago_inscripcion', function (Blueprint $table) {
            $table->unsignedInteger('Id_pago');
            $table->unsignedInteger('Codigo_inscripcion');
            $table->string('estado_pago_inscripcion', 20)->default('Pendiente');

            $table->date('fecha_pago')->nullable();

            $table->unsignedInteger('Id_comprobante')->nullable();

            $table->primary(['Id_pago', 'Codigo_inscripcion']);

            $table->foreign('Id_pago')
                ->references('Id_pago')
                ->on('pago')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('Codigo_inscripcion')
                ->references('Codigo_inscripcion')
                ->on('inscripcion')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('Id_comprobante')
                ->references('Id_comprobante')
                ->on('comprobante')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });

        Schema::create('docente_especialidad', function (Blueprint $table) {
            $table->unsignedInteger('Id_docente');
            $table->unsignedInteger('Id_especialidad');

            $table->primary(['Id_docente', 'Id_especialidad']);

            $table->foreign('Id_docente')
                ->references('Id_docente')
                ->on('docente')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('Id_especialidad')
                ->references('Id_especialidad')
                ->on('especialidad')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
        Schema::create('grupo', function (Blueprint $table) {
            $table->increments('Id_grupo');
            $table->string('sigla_grupo', 50);
            $table->integer('capacidad_max');
            $table->string('estado', 20);
            $table->integer('cant_estudiantes');

            $table->unsignedInteger('Id_aula');
            $table->unsignedInteger('Id_modalidad');
            $table->unsignedInteger('Id_turno');
            $table->unsignedInteger('Id_gestion');

            $table->foreign('Id_aula')
                ->references('Id_aula')
                ->on('aula')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('Id_modalidad')
                ->references('Id_modalidad')
                ->on('modalidad')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('Id_turno')
                ->references('Id_turno')
                ->on('turno')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('Id_gestion')
                ->references('Id_gestion')
                ->on('gestion')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        Schema::create('grupo_horario', function (Blueprint $table) {
            $table->unsignedInteger('Id_grupo');
            $table->unsignedInteger('Id_horario');

            $table->primary(['Id_grupo', 'Id_horario']);

            $table->foreign('Id_grupo')
                ->references('Id_grupo')
                ->on('grupo')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('Id_horario')
                ->references('Id_horario')
                ->on('horario')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        Schema::create('grupo_materia', function (Blueprint $table) {
            $table->unsignedInteger('Id_grupo');
            $table->unsignedInteger('Id_materia');
            $table->unsignedInteger('Id_docente');

            $table->primary(['Id_grupo', 'Id_materia']);

            $table->foreign('Id_grupo')
                ->references('Id_grupo')
                ->on('grupo')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('Id_materia')
                ->references('Id_materia')
                ->on('materia')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('Id_docente')
                ->references('Id_docente')
                ->on('docente')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        Schema::create('evaluacion', function (Blueprint $table) {
            $table->increments('Id_evaluacion');
            $table->integer('numero_evaluacion');
            $table->decimal('porcentaje', 5, 2);
            $table->date('fecha');
            $table->string('estado', 20);
            $table->unsignedInteger('Id_grupo');
            $table->unsignedInteger('Id_materia');

            $table->foreign(['Id_grupo', 'Id_materia'])
                ->references(['Id_grupo', 'Id_materia'])
                ->on('grupo_materia')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        Schema::create('grupo_postulante', function (Blueprint $table) {
            $table->unsignedInteger('Id_grupo');
            $table->unsignedInteger('Id_postulante');
            $table->string('estado', 20);
            $table->date('fecha_asignacion')->default(DB::raw('CURRENT_DATE'));

            $table->primary(['Id_grupo', 'Id_postulante']);

            $table->foreign('Id_grupo')
                ->references('Id_grupo')
                ->on('grupo')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('Id_postulante')
                ->references('Id_postulante')
                ->on('postulante')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        Schema::create('nota', function (Blueprint $table) {
            $table->increments('Id_nota');
            $table->decimal('nota', 5, 2);
            $table->string('estado_academico', 20);
            $table->date('fecha')->default(DB::raw('CURRENT_DATE'));
            $table->unsignedInteger('Id_evaluacion');
            $table->unsignedInteger('Id_grupo');
            $table->unsignedInteger('Id_postulante');

            $table->foreign('Id_evaluacion')
                ->references('Id_evaluacion')
                ->on('evaluacion')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign(['Id_grupo', 'Id_postulante'])
                ->references(['Id_grupo', 'Id_postulante'])
                ->on('grupo_postulante')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        Schema::create('resultadoacademico', function (Blueprint $table) {
            $table->increments('Id_resultado');
            $table->decimal('promedio_final', 5, 2);
            $table->string('estado_final', 20);
            $table->date('fecha_calculo')->default(DB::raw('CURRENT_DATE'));
            $table->unsignedInteger('Id_postulante');

            $table->foreign('Id_postulante')
                ->references('Id_postulante')
                ->on('postulante')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        Schema::create('asistencia', function (Blueprint $table) {
            $table->increments('Id_asistencia');
            $table->date('fecha')->default(DB::raw('CURRENT_DATE'));
            $table->time('hora')->default(DB::raw('CURRENT_TIME'));
            $table->string('estado', 20);
            $table->text('observacion')->nullable();
            $table->unsignedInteger('Id_grupo');
            $table->unsignedInteger('Id_materia');
            $table->unsignedInteger('Id_postulante');

            $table->foreign(['Id_grupo', 'Id_materia'])
                ->references(['Id_grupo', 'Id_materia'])
                ->on('grupo_materia')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign(['Id_grupo', 'Id_postulante'])
                ->references(['Id_grupo', 'Id_postulante'])
                ->on('grupo_postulante')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('preferencia_inscripcion');
        Schema::dropIfExists('asistencia');
        Schema::dropIfExists('resultadoacademico');
        Schema::dropIfExists('nota');
        Schema::dropIfExists('grupo_postulante');
        Schema::dropIfExists('evaluacion');
        Schema::dropIfExists('grupo_materia');
        Schema::dropIfExists('grupo_horario');
        Schema::dropIfExists('grupo');
        Schema::dropIfExists('docente_especialidad');
        Schema::dropIfExists('pago');
        Schema::dropIfExists('cupocarrera');
        Schema::dropIfExists('persona_documento');
        Schema::dropIfExists('documento');
        Schema::dropIfExists('inscripcion_carrera');
        Schema::dropIfExists('inscripcion');
        Schema::dropIfExists('detalle_bitacora');
        Schema::dropIfExists('bitacora');
        Schema::dropIfExists('reporte');
        Schema::dropIfExists('usuario');
        Schema::dropIfExists('postulante');
        Schema::dropIfExists('asignacioncupo');
        Schema::dropIfExists('docente');
        Schema::dropIfExists('administrador');
        Schema::dropIfExists('superadministrador');
        Schema::dropIfExists('comprobante');
        Schema::dropIfExists('materia');
        Schema::dropIfExists('horario');
        Schema::dropIfExists('turno');
        Schema::dropIfExists('modalidad');
        Schema::dropIfExists('aula');
        Schema::dropIfExists('especialidad');
        Schema::dropIfExists('gestion');
        Schema::dropIfExists('carrera');
        Schema::dropIfExists('persona');
    }
};
