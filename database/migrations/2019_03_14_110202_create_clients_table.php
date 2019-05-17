<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->increments('id');
            $table->string('lrs_id', 100)->unique();
            $table->foreign('lrs_id')->references('_id')->on('lrs');
            $table->string('api_basic_key', 100);
            $table->string('api_basic_secret', 100);
            $table->string('authority_name', 255);
            $table->string('authority_mbox', 255);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clients');
    }
}
