<?php

namespace Database\Seeders;

use App\Models\CurrencyExchangeRate;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CurrencyExchangeRateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $currencyRate = [
            ['id' => 1, 'range_low' => '6', 'range_high' => '7','dollar'=>'1','product_id' => '6','methods' => 'alipay'],
            ['id' => 2, 'range_low' => '30', 'range_high' => '40','dollar'=>'5','product_id' => '1','methods' => 'alipay'],
            ['id' => 3, 'range_low' => '60', 'range_high' => '80','dollar'=>'10','product_id' => '2','methods' => 'alipay'],
            ['id' => 6, 'range_low' => '130', 'range_high' => '160','dollar'=>'20','product_id' => '3','methods' => 'alipay'],
            ['id' => 7, 'range_low' => '30', 'range_high' => '40','dollar'=>'5','product_id' => '7','methods' => 'unipay'],
            ['id' => 8, 'range_low' => '60', 'range_high' => '80','dollar'=>'10','product_id' => '8','methods' => 'unipay'],
            ['id' => 9, 'range_low' => '130', 'range_high' => '160','dollar'=>'20','product_id' => '9','methods' => 'unipay'],
            ['id' => 10, 'range_low' => '320', 'range_high' => '390','dollar'=>'50','product_id' => '10','methods' => 'unipay'],
            ['id' => 11, 'range_low' => '630', 'range_high' => '770','dollar'=>'100','product_id' => '11','methods' => 'unipay'],
            ['id' => 12, 'range_low' => '1260', 'range_high' => '1540','dollar'=>'200','product_id' => '12','methods' => 'unipay'],
            ['id' => 13, 'range_low' => '30', 'range_high' => '40','dollar'=>'5','product_id' => '13','methods' => 'card'],
            ['id' => 14, 'range_low' => '60', 'range_high' => '80','dollar'=>'10','product_id' => '14','methods' => 'card'],
            ['id' => 15, 'range_low' => '130', 'range_high' => '160','dollar'=>'20','product_id' => '15','methods' => 'card'],
            ['id' => 16, 'range_low' => '320', 'range_high' => '390','dollar'=>'50','product_id' => '16','methods' => 'card'],
            ['id' => 17, 'range_low' => '630', 'range_high' => '770','dollar'=>'100','product_id' => '17','methods' => 'card'],
            ['id' => 18, 'range_low' => '1260', 'range_high' => '1540','dollar'=>'200','product_id' => '18','methods' => 'card']
];
        CurrencyExchangeRate::upsert($currencyRate,['id']);
    }
}
