<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Imports\SatProductKeyImport;
use Maatwebsite\Excel\Facades\Excel;

class ImportSatProductKeys extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:sat-product-keys';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importa el catálogo de claves de productos del SAT desde un archivo Excel';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando la importación del catálogo de claves de productos del SAT...');
        
        // Usamos la función helper storage_path() para obtener la ruta absoluta
        $catalogPath = storage_path('app/catalogs');
        
        // ✅ --- CORRECCIÓN AQUÍ --- ✅
        // Ahora buscamos el archivo con la doble extensión que encontramos.
        $fileName = 'sat_product_keys.xlsx.xls';
        $filePath = $catalogPath . '/' . $fileName;

        // 1. Verificar si el directorio existe usando una función nativa de PHP
        if (!is_dir($catalogPath)) {
            $this->error("El directorio no existe en la ruta absoluta: {$catalogPath}");
            return 1;
        }
        $this->info("Directorio encontrado en: {$catalogPath}");

        // 2. Mostrar los archivos encontrados en el directorio
        $filesInDir = array_diff(scandir($catalogPath), ['.', '..']);
        if (empty($filesInDir)) {
            $this->warn("El directorio está vacío.");
        } else {
            $this->info("Archivos encontrados:");
            foreach ($filesInDir as $file) {
                $this->line('- ' . $file);
            }
        }

        // 3. Verificar si el archivo específico existe
        if (!file_exists($filePath)) {
            $this->error("El archivo no se encontró en: {$filePath}");
            $this->warn("Asegúrate de que el nombre y la extensión sean exactos.");
            return 1;
        }

        // Si el archivo existe, procede con la importación
        $this->info("Archivo encontrado. Iniciando importación...");
        Excel::import(new SatProductKeyImport, $filePath);

        $this->info('¡Importación completada exitosamente!');
        return 0;
    }
}