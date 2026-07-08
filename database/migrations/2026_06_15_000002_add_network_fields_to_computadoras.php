<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ss_computadoras', function (Blueprint $table) {
            $table->string('direccion_ip', 45)->nullable()->after('departamento')
                  ->comment('Dirección IPv4 o IPv6 de la computadora');
            $table->string('direccion_mac', 17)->nullable()->after('direccion_ip')
                  ->comment('Dirección MAC en formato AA:BB:CC:DD:EE:FF');
        });
    }

    public function down(): void
    {
        Schema::table('ss_computadoras', function (Blueprint $table) {
            $table->dropColumn(['direccion_ip', 'direccion_mac']);
        });
    }
};
