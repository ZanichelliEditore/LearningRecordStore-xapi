<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterStructureClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign('clients_lrs_id_foreign');
            $table->dropUnique(['lrs_id']);
            $table->unique(['api_basic_key']);
            $table->foreign('lrs_id')->references('_id')->on('lrs');
            $table->foreign('api_basic_key')->references('id')->on('oauth_clients')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
    */
    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign('clients_api_basic_key_foreign');
            $table->dropUnique(['api_basic_key']);
            $table->unique(['lrs_id']);
        });
    }
}
