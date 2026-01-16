<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        try {
            DB::statement('INSERT IGNORE INTO programa_competencia (id_prog_fk, cod_comp_fk) SELECT id_prog_fk, cod_comp FROM competencia');
        } catch (\Throwable $e) {
            // En algunos motores no existe IGNORE; intentar alternativa
            try {
                DB::statement('INSERT INTO programa_competencia (id_prog_fk, cod_comp_fk) SELECT id_prog_fk, cod_comp FROM competencia');
            } catch (\Throwable $e2) {
                // silenciar si ya existen filas
            }
        }
    }

    public function down(): void
    {
        // No borrar datos en down
    }
};
