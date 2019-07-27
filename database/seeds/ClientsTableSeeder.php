<?php

use Faker\Factory;
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
        DB::table('clients')->insert([
            [
                'lrs_id' => '5c0e80df351947fd1766e6ae',
                'api_basic_key' => 'a35e7f6adff618225932f456dac4bff5c0321a36', 
                'api_basic_secret' => 'BnlL4zpW1CrPTdDrjehr5s0OHzae8hsz27FshsjL', 
                'authority_name' => 'digitals', 
                'authority_mbox' => 'mbox:digitals@example.com',
                'scopes' => '["statements\/read"]'                
            ],
            [
                'lrs_id' => '1234567890', 
                'api_basic_key' => '85834ea3f1150032809f16ab1d4ec194b1ec8608', 
                'api_basic_secret' => 'PxEr4aRcHs4Tnfz7BatQqVoovCqSxXbqXKcmeJom', 
                'authority_name' => 'datalake', 
                'authority_mbox' => 'mbox:datalake@example.com',
                'scopes' => '["statements\/read"]'
            ]
        ]);
    }
}
