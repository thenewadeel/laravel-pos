<?php

namespace App\Imports;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;


class UsersImport implements WithUpserts, WithHeadingRow
{
    /**
     * @return string|array
     */
    public function uniqueBy()
    {
        return 'email';
    }
    /**
     * @param array $row
     *
     * @return User|null
     */
    // public function model(array $row)
    // {
    //     // dd($row);
    //     return new User([
    //         'first_name' => $row['first_name'],
    //         'last_name' => $row['last_name'],
    //         'email' => $row['email'],
    //         'type' => $row['type'],
    //         'created_at' => $row['created_at'],


    //         // 'first_name'     => $row[__('user.FName')],
    //         // 'last_name'     => $row[__('user.LName')],
    //         // 'email'    => $row[__('user.Email')],
    //         // 'type'    => $row[__('user.Type')],
    //         // 'created_at' => $row[__('user.Created At')],

    //         // 'first_name'     => $row[0],
    //         // 'last_name'     => $row[1],
    //         // 'email'    => $row[2],
    //         // 'type'    => $row[3],
    //         // 'created_at' => $row[4],
    //         'password' => bcrypt(config('settings.default_password', '12345678')),
    //     ]);
    // }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function onRow(Row $row)
    {
        // dd($row);
        $user =
            User::updateOrCreate([
                'email' => $row['email'],
            ], [
                'first_name' => $row['first_name'],
                'last_name' => $row['last_name'],
                'type' => $row['type'],
                'created_at' => $row['created_at'],
                'password' => bcrypt(config('settings.default_password', '12345678')),
            ]);
        $shops = explode(',', $row['shops']);
        foreach ($shops as $shop) {
            $user->shops()->sync(
                Shop::firstOrCreate([
                    'name' => $shop,
                ])
            );
        }
        // Log::info("User imported: " . $user->id);
        return $user;
    }
}
