<?php

namespace Database\Seeders;

use App\Models\Bank;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $banks = [
            ['id' => 1, 'account_type' => 'Alipay', 'status' => 'active', 'created_at' => '2023-01-30 12:51:46','updated_at'=>'2023-01-30 12:51:46','deleted_at' => NULL],
            ['id' => 2, 'account_type' => 'Bank Account', 'status' => 'active', 'created_at' => '2023-01-30 12:51:46','updated_at'=>'2023-01-30 12:51:46','deleted_at' => NULL],
            ['id' => 3, 'account_type' => 'WeChat', 'status' => 'active', 'created_at' => '2023-01-30 12:51:46','updated_at'=>'2023-01-30 12:51:46','deleted_at' => NULL],
            ['id' => 4, 'account_type' => 'Ipay88', 'status' => 'active', 'created_at' => '2023-01-30 12:51:46','updated_at'=>'2023-01-30 12:51:46','deleted_at' => NULL]
];
        Bank::upsert($banks,['id']);
    }
}
