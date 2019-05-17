<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PassportClientsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('oauth_clients')->insert([[
            'id' => 'a35e7f6adff618225932f456dac4bff5c0321a36', 
            'user_id' => NULL, 'secret' => 'BnlL4zpW1CrPTdDrjehr5s0OHzae8hsz27FshsjL', 
            'name' => 'testuser1', 
            'redirect' => 'http://localhost', 
            'personal_access_client' => 0, 
            'password_client' => 1, 
            'revoked' => 0
        ],
        [
            'id' => '435e70a3f8fdcbf3a727e1c2e9885bfb681f0820', 
            'user_id' => NULL, 
            'secret' => 'Jw5qlIAQVfotaUQGO3cxvNLY76WFVY6KCA01Oqdu', 
            'name' => 'testuser2', 
            'redirect' => 'http://localhost', 
            'personal_access_client' => 0, 
            'password_client' => 1, 
            'revoked' => 0
        ],
        [
            'id' => '85834ea3f1150032809f16ab1d4ec194b1ec8608', 
            'user_id' => NULL, 
            'secret' => 'PxEr4aRcHs4Tnfz7BatQqVoovCqSxXbqXKcmeJom', 
            'name' => 'testuser3', 
            'redirect' => 'http://localhost', 
            'personal_access_client' => 0, 
            'password_client' => 1, 
            'revoked' => 0
        ]]);
    }
}
