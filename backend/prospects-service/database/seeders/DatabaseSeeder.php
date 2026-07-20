<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $total = 6000;
        $chunkSize = 1000;
        
        $names = ["Juan", "Maria", "Carlos", "Ana", "Luis", "Jose", "Pedro", "Sofia", "Diego", "Lucia"];
        $surnames = ["Perez", "Gomez", "Rodriguez", "Lopez", "Garcia", "Martinez", "Sanchez", "Fernandez", "Gonzalez", "Diaz"];
        
        // Disable foreign key checks to safely truncate and insert
        DB::statement("SET FOREIGN_KEY_CHECKS = 0");
        
        DB::statement("DELETE FROM prospects_db.prospectos WHERE id > 7");
        DB::statement("DELETE FROM sales_db.prospectos WHERE id > 7");
        DB::statement("DELETE FROM dashboard_db.prospectos WHERE id > 7");
        
        $chunks = [];
        for ($i = 8; $i <= $total; $i++) {
            $name = $names[array_rand($names)] . ' ' . $surnames[array_rand($surnames)] . ' ' . $i;
            $email = "prospecto{$i}@example.com";
            $phone = "+519" . rand(10000000, 99999999);
            $vehiculo_id = rand(1, 5);
            $etapa = ['prospeccion', 'calificacion', 'negociacion', 'cierre'][rand(0, 3)];
            $empleado_id = rand(1, 3);
            
            $chunks[] = [
                'id' => $i,
                'nombre' => $name,
                'email' => $email,
                'telefono' => $phone,
                'vehiculo_id' => $vehiculo_id,
                'etapa' => $etapa,
                'empleado_id' => $empleado_id,
            ];
            
            if (count($chunks) >= $chunkSize || $i === $total) {
                // Insert chunks in all three DBs
                DB::table('prospects_db.prospectos')->insert($chunks);
                DB::table('sales_db.prospectos')->insert($chunks);
                DB::table('dashboard_db.prospectos')->insert($chunks);
                $chunks = [];
            }
        }
        
        DB::statement("SET FOREIGN_KEY_CHECKS = 1");
    }
}
