<?php

use Illuminate\Database\Seeder;

class LrsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $sql = 'INSERT INTO lrs (_id, title, folder, description) VALUES
        ("58a1d168351947b0147b23ca", "LoSai?", "losai", "Lrs repository di LoSai"),
        ("1234567890", "LRS_TEST", "lrs_test", "Lrs per i test"),
        ("59fada03351947bc75d6fea4", "MyZanichelli", "myz", "Lrs repository di Myz"),
        ("5c0e80df351947fd1766e6ae", "Collezioni Test", "collezioni_test", "Lrs repository di Test di Collezioni,Collezioni-Univ,Biblioteca"),
        ("5911d2eb351947e90478db45", "CreaVerifiche", "creaverifiche", "Lrs repository di Creaverifiche")';

        DB::statement($sql);
    }
}
