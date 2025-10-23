<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->integer('usuario_id'); // sin foreign key

            $table->string('tipo', 50); // 'planilla', 'pago', 'alerta', etc.
            $table->string('titulo', 255);
            $table->text('mensaje');
            $table->string('icono', 50)->nullable(); // Clase FontAwesome
            $table->string('color', 20)->default('info'); // success, warning, danger, info
            $table->string('url', 500)->nullable();
            $table->boolean('leida')->default(false);
            $table->timestamp('leida_en')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            // índices para búsquedas rápidas
            $table->index(['usuario_id', 'leida']);
            $table->index(['tipo', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('notificaciones');
    }
};
