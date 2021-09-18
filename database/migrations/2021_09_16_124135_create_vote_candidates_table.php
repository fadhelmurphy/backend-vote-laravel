<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVoteCandidatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vote_candidates', function (Blueprint $table) {
            // fields
            $table->id();
            $table->bigInteger('id_vote')->unsigned();
            $table->string('name', 32);
            $table->string('image', 32)->nullable(true);
            $table->timestamps();

            // id and relations
            $table->unique(['id', 'id_vote']);
            $table->foreign('id_vote')->references('id')->on('votes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vote_candidates');
    }
}
