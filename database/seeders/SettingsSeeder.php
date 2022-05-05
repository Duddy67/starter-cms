<?php

namespace Database\Seeders;
use App\Models\Settings\General;

use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        General::insert([
            ['group' => 'app', 'key' => 'name', 'value' => 'Starter CMS'],
            ['group' => 'app', 'key' => 'timezone', 'value' => 'Europe/Paris'],
            ['group' => 'app', 'key' => 'date_format', 'value' => 'd/m/Y H:i'],
            ['group' => 'pagination', 'key' => 'per_page', 'value' => '5']
        ]);
    }
}
