<?php

namespace App\Imports;

use App\Models\SatProductKey;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class SatProductKeyImport implements ToModel, WithHeadingRow, WithChunkReading
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Asegúrate de que los nombres 'clave' y 'descripcion' coincidan
        // con los encabezados de tu archivo de Excel.
        return new SatProductKey([
            'code' => $row['clave'],
            'name' => $row['descripcion'],
        ]);
    }

    public function chunkSize(): int
    {
        // Procesa el archivo en trozos de 1000 filas para no agotar la memoria.
        return 1000;
    }
}