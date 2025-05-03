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
            ['key' => 'default_tax_rate', 'value' => '17'],
            ['key' => 'dark_theme', 'value' => '0'],
            ['key' => 'club_name', 'value' => env('CLUB_NAME', 'WT Club')],
            ['key' => 'club_initials', 'value' => env('CLUB_INITIALS', 'WTC')],
            ['key' => 'club_address_line_1', 'value' => env('CLUB_ADDRESS_LINE_1', 'Wittness Tech')],
            ['key' => 'club_address_line_2', 'value' => env('CLUB_ADDRESS_LINE_2', 'Lahore')],
            ['key' => 'club_web_address', 'value' => env('CLUB_WEB_ADDRESS', 'wittness.tech')],
            ['key' => 'club_email', 'value' => env('CLUB_EMAIL', 'info@wittness.tech')],
            ['key' => 'social_facebook_url', 'value' => env('SOCIAL_FACEBOOK', 'facebook.com/wittness.tech')],
            ['key' => 'social_x_url', 'value' => env('SOCIAL_X', 'x.com/wittness.tech')],
            ['key' => 'social_youtube_url', 'value' => env('SOCIAL_YOUTUBE', 'youtube.com/@wittnesstech')],
            ['key' => 'club_logo', 'value' => env('CLUB_LOGO_PATH', 'public/logo.png')],
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
