<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Laboratory;

class LaboratorySeeder extends Seeder
{
    public function run()
    {
        Laboratory::create(['name' => 'Laboratorio 1']);
        Laboratory::create(['name' => 'Laboratorio 2']);
        Laboratory::create(['name' => 'Laboratorio 3']);
        Laboratory::create(['name' => 'Laboratorio 5']);
        Laboratory::create(['name' => 'Laboratorio 6']);
        Laboratory::create(['name' => 'Laboratorio 7']);
        Laboratory::create(['name' => 'Laboratorio 8']);
        Laboratory::create(['name' => 'Laboratorio 9']);
        Laboratory::create(['name' => 'Laboratorio 10']);
        Laboratory::create(['name' => 'Laboratorio 11']);
        Laboratory::create(['name' => 'Laboratorio 12']);
        Laboratory::create(['name' => 'Laboratorio 13']);
        Laboratory::create(['name' => 'Laboratorio 14']);
        Laboratory::create(['name' => 'Laboratorio 15']);
    }
}