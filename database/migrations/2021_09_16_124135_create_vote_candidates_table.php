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
            $table->engine = 'InnoDB';
            // fields
            $table->id();
            $table->bigInteger('id_vote')->unsigned();
            $table->string('name', 32);
            $table->string('image', 72)->nullable(true);
            $table->timestamps();

            // id and relations
            $table->unique(['id', 'id_vote']);
            $table->foreign('id_vote')->references('id')->on('votes')->onUpdate('cascade')->onDelete('cascade');
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
