<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserVotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_votes', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            // fields
            $table->id();
            $table->bigInteger('id_user')->unsigned();
            $table->bigInteger('id_vote')->unsigned();
            $table->bigInteger('id_candidate')->unsigned();
            $table->timestamps();

            // id and relations
            $table->foreign('id_user')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('id_vote')->references('id')->on('votes')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('id_candidate')->references('id')->on('vote_candidates')->onUpdate('cascade')->onDelete('cascade');
            $table->unique(['id_user', 'id_vote']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_votes');
    }
}
