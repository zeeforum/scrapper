<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('imdb_title_episodes', function (Blueprint $table) {
            $table->id();
            $table->string('tconst')->index();
            $table->string('parent_tconst')->index();
            $table->integer('season_number')->nullable()->default(null)->index();
            $table->integer('episode_number')->nullable()->default(null)->index();
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
        Schema::dropIfExists('imdb_title_episodes');
    }
};
