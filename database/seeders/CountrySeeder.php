<?php

namespace Database\Seeders;

use App\Country;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $csvFile = fopen(base_path("public/csv/countries.csv"), "r");

        $firstline = true;
        while (($data = fgetcsv($csvFile, 2000, ",")) !== FALSE) {

            if (!$firstline) {
                Country::create([
                    "id" => $data['0'],
                    "name" => $data['1'],
                    "code" => $data['2']
                ]);   
                // echo $data[1]; 
            }
            $firstline = false;
        }
        fclose($csvFile);
    }
}
