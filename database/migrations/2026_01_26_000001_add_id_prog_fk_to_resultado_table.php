<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('resultado', function (Blueprint $table) {
            $table->integer('id_prog_fk')->nullable()->after('cod_comp_fk')->index();
        });
    }

    public function down(): void
    {
        Schema::table('resultado', function (Blueprint $table) {
            $table->dropColumn('id_prog_fk');
        });
    }
};
