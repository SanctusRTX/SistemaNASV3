<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ss_computadoras', function (Blueprint $table) {
            $table->id();

            // ── Identificación ──────────────────────────────────────────
            $table->string('nombre', 100)->comment('Nombre o código de la computadora');
            $table->string('numero_serie', 100)->nullable()->comment('Número de serie');
            $table->string('codigo_inventario', 100)->nullable()->comment('Código de inventario institucional');

            // ── Hardware ────────────────────────────────────────────────
            $table->string('marca', 80)->nullable();
            $table->string('modelo', 100)->nullable();
            $table->string('tipo', 50)->default('Desktop')
                ->comment('Desktop, Laptop, All-in-One, Servidor, Workstation');
            $table->string('procesador', 150)->nullable();
            $table->string('ram', 50)->nullable()->comment('Ej: 8 GB DDR4');
            $table->string('almacenamiento', 100)->nullable()->comment('Ej: 500 GB SSD');
            $table->string('tarjeta_grafica', 150)->nullable();
            $table->string('sistema_operativo', 100)->nullable();

            // ── Operador ────────────────────────────────────────────────
            $table->string('operador', 150)->comment('Nombre del operador / usuario asignado');
            $table->string('cargo_operador', 100)->nullable()->comment('Cargo del operador');

            // ── Departamento ────────────────────────────────────────────
            $table->string('departamento', 150)->comment('Departamento al que pertenece');

            // ── Estado ──────────────────────────────────────────────────
            $table->string('estado', 50)->default('Activo')
                ->comment('Activo, En reparación, Dado de baja');
            $table->text('observaciones')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ss_computadoras');
    }
};
