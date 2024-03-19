<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;


class ExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = \App\Models\User::all();
        $heads = ['Food', 'Transportation', 'Entertainment', 'Gifts', 'Miscellaneous'];
        $monthAgo = now()->subMonth();

        foreach (range(1, 50) as $_) {
            $randomUser = $users->random();
            $randomHead = $heads[rand(0, count($heads) - 1)];
            $randomAmount = rand(1, 500) / 100;

            \App\Models\Expense::create([
                'user_id' => $randomUser->id,
                'head' => $randomHead,
                'amount' => $randomAmount,
                'notes' => null,
                'created_at' => $monthAgo->addMinutes(rand(1, 1440))
            ]);
        }
    }
}
