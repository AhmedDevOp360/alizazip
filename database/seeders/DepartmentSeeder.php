<?php

namespace Database\Seeders;

use App\Department;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $csvFile = fopen(base_path("public/csv/departments.csv"), "r");

        $firstline = true;
        while (($data = fgetcsv($csvFile, 2000, ",")) !== FALSE) {

            if (!$firstline) {
                Department::create([
                    // "id" => $data['0'],
                    "name" => mb_convert_encoding($data['1'], 'UTF-8', 'auto'),
                    "code" => $data['2'],
                    "country_id" => 46
                ]);   
                // echo $data[1]; 
            }
            $firstline = false;
        }
        fclose($csvFile);
    }
}
