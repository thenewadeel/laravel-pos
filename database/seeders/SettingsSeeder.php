<?php

namespace Database\Seeders;

use App\Models\Setting;
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
        $data = [
            ['key' => 'app_name', 'value' => env('APP_NAME', 'Laravel-POS'),],
            ['key' => 'currency_symbol', 'value' => 'Rs '],
            ['key' => 'default_printer_ip', 'value' => '192.168.0.162'],
        ];

        foreach ($data as $value) {
            Setting::Create([
                'key' => $value['key'],
                'value' => $value['value'],
                'user_id' => '1'
            ]);
        }
    }
}
