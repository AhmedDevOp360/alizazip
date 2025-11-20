<?php

namespace Database\Seeders;

use App\Municipality;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MunicipalitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $csvFile = fopen(base_path("public/csv/municipalities.csv"), "r");

        $firstline = true;
        while (($data = fgetcsv($csvFile, 2000, ",")) !== FALSE) {

            if (!$firstline) {
                Municipality::create([
                    // "id" => $data['0'],
                    "name" => mb_convert_encoding($data['1'], 'UTF-8', 'auto'),
                    "code" => $data['2'],
                    "codefacturador" => $data['3'],
                    "department_id" => $data['4'],
                ]);   
                // echo $data[1]; 
            }
            $firstline = false;
        }
        fclose($csvFile);
    }
}
