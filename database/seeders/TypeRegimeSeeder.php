<?php

namespace Database\Seeders;

use App\TypeRegime;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TypeRegimeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $csvFile = fopen(base_path("public/csv/type_regimes.csv"), "r");

        $firstline = true;
        while (($data = fgetcsv($csvFile, 2000, ",")) !== FALSE) {

            if (!$firstline) {
                TypeRegime::create([
                    // "id" => $data['0'],
                    "name" => mb_convert_encoding($data['1'], 'UTF-8', 'auto'),
                    "code" => $data['2'],
                ]);   
                // echo $data[1]; 
            }
            $firstline = false;
        }
        fclose($csvFile);
    }
}
