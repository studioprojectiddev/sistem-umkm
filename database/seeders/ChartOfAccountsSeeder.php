<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ChartOfAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = [
            // ASSET
            ['code' => '1-1000', 'name' => 'Kas', 'type' => 'asset', 'parent_id' => null, 'children' => []],
            ['code' => '1-1100', 'name' => 'Bank', 'type' => 'asset', 'parent_id' => null, 'children' => []],
            ['code' => '1-1200', 'name' => 'Piutang Usaha', 'type' => 'asset', 'parent_id' => null, 'children' => []],
            ['code' => '1-1300', 'name' => 'Persediaan', 'type' => 'asset', 'parent_id' => null, 'children' => []],
            ['code' => '1-1400', 'name' => 'Aset Tetap', 'type' => 'asset', 'parent_id' => null, 'children' => []],

            // LIABILITY
            ['code' => '2-2000', 'name' => 'Hutang Usaha', 'type' => 'liability', 'parent_id' => null, 'children' => []],
            ['code' => '2-2100', 'name' => 'Hutang Pajak', 'type' => 'liability', 'parent_id' => null, 'children' => []],

            // EQUITY
            ['code' => '3-3000', 'name' => 'Modal', 'type' => 'equity', 'parent_id' => null, 'children' => []],
            ['code' => '3-3100', 'name' => 'Laba Ditahan', 'type' => 'equity', 'parent_id' => null, 'children' => []],

            // REVENUE
            ['code' => '4-4000', 'name' => 'Penjualan', 'type' => 'revenue', 'parent_id' => null, 'children' => []],
            ['code' => '4-4100', 'name' => 'Pendapatan Lain-lain', 'type' => 'revenue', 'parent_id' => null, 'children' => []],

            // EXPENSE
            ['code' => '5-5000', 'name' => 'Beban Operasional', 'type' => 'expense', 'parent_id' => null, 'children' => []],
            ['code' => '5-5100', 'name' => 'Beban Gaji', 'type' => 'expense', 'parent_id' => null, 'children' => []],
            ['code' => '5-5200', 'name' => 'Beban Listrik & Air', 'type' => 'expense', 'parent_id' => null, 'children' => []],
            ['code' => '5-5300', 'name' => 'HPP', 'type' => 'expense', 'parent_id' => null, 'children' => []],
        ];

        foreach ($accounts as $account) {
            $parent = \DB::table('chart_of_accounts')->insertGetId([
                'code' => $account['code'],
                'name' => $account['name'],
                'type' => $account['type'],
                'parent_id' => $account['parent_id'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($account['children'] as $child) {
                \DB::table('chart_of_accounts')->insert([
                    'code' => $child['code'],
                    'name' => $child['name'],
                    'type' => $child['type'],
                    'parent_id' => $parent,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
