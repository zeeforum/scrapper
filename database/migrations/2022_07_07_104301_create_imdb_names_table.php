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
        Schema::create('imdb_names', function (Blueprint $table) {
            $table->id();
            $table->string('nconst')->index();
            $table->string('primary_name')->index();
            $table->integer('birth_year')->nullable()->default(null)->index();
            $table->integer('death_year')->nullable()->default(null)->index();
            $table->string('primary_profession')->nullable()->default(null);
            $table->text('known_for_titles')->nullable()->default(null);
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
        Schema::dropIfExists('imdb_names');
    }
};
