<?php

use Faker\Factory;
use App\Models\Lrs;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClientsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Factory::create();
        DB::table('clients')->insert([[
            'lrs_id' => $faker->randomElement(Lrs::pluck('_id')->toArray()),
            'api_basic_key' => 'a35e7f6adff618225932f456dac4bff5c0321a36', 
            'api_basic_secret' => 'BnlL4zpW1CrPTdDrjehr5s0OHzae8hsz27FshsjL', 
            'authority_name' => 'digitals', 
            'authority_mbox' => 'mbox:digitals@example.com'
        ],
        [
            'lrs_id' => $faker->randomElement(Lrs::pluck('_id')->toArray()), 
            'api_basic_key' => '435e70a3f8fdcbf3a727e1c2e9885bfb681f0820', 
            'api_basic_secret' => 'Jw5qlIAQVfotaUQGO3cxvNLY76WFVY6KCA01Oqdu', 
            'authority_name' => 'secondini', 
            'authority_mbox' => 'mbox:secondini@example.com'
        ],
        [
            'lrs_id' => '1234567890', 
            'api_basic_key' => '85834ea3f1150032809f16ab1d4ec194b1ec8608', 
            'api_basic_secret' => 'PxEr4aRcHs4Tnfz7BatQqVoovCqSxXbqXKcmeJom', 
            'authority_name' => 'datalake', 
            'authority_mbox' => 'mbox:datalake@example.com'
        ]]);
    }
}
