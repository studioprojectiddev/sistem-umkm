<?php

namespace Database\Seeders;
use App\Models\Account;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Account::create([
            'name' => 'Cash',
            'type' => 'cash',
            'initial_balance' => 0
        ]);

        Account::create([
            'name' => 'BCA',
            'type' => 'bank',
            'initial_balance' => 0
        ]);

        Account::create([
            'name' => 'OVO',
            'type' => 'ewallet',
            'initial_balance' => 0
        ]);
    }
}
