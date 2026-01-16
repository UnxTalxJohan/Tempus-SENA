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
        if (!Schema::hasTable('programa_competencia')) {
            Schema::create('programa_competencia', function (Blueprint $table) {
                $table->integer('id_prog_fk');
                $table->integer('cod_comp_fk');
                $table->primary(['id_prog_fk', 'cod_comp_fk'], 'pk_prog_comp');
                $table->index('id_prog_fk');
                $table->index('cod_comp_fk');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programa_competencia');
    }
};
