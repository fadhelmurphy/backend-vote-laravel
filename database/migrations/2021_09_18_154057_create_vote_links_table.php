<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVoteLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vote_links', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            // fields
            $table->id();
            $table->bigInteger('id_vote')->unsigned();
            $table->bigInteger('id_link')->unsigned();
            $table->timestamps();

            // id and relations
            // $table->unique('id_vote', 'id_link');
            $table->unique('id');
            $table->foreign('id_vote')->references('id')->on('votes')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('id_link')->references('id')->on('links')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vote_links');
    }
}
