<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('servicios', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            $table->decimal('precio', 10, 2)->default(0);
            $table->unsignedTinyInteger('estado')->default(1);
            $table->timestamps();
        });

        Schema::table('detalles_venta', function (Blueprint $table) {
            $table->foreignId('id_servicio')->nullable()->after('id_venta')->constrained('servicios');
            $table->decimal('cantidad_servicio', 10, 2)->nullable()->after('id_servicio');
        });

        Schema::table('detalles_venta', function (Blueprint $table) {
            $table->unsignedBigInteger('id_producto')->nullable()->change();
            $table->unsignedBigInteger('id_lote')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detalles_venta', function (Blueprint $table) {
            $table->unsignedBigInteger('id_producto')->nullable(false)->change();
            $table->unsignedBigInteger('id_lote')->nullable(false)->change();
        });

        Schema::table('detalles_venta', function (Blueprint $table) {
            $table->dropConstrainedForeignId('id_servicio');
            $table->dropColumn('cantidad_servicio');
        });

        Schema::dropIfExists('servicios');
    }
};
