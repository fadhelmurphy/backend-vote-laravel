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
            $table->id();
            $table->string('id_url', 6);
            $table->string('email', 60);
            $table->string('id_vote', 6);
            $table->string('votename', 30);
            $table->string('status', 7);
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
        Schema::dropIfExists('vote_links');
    }
}
