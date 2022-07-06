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
        Schema::create('imdb_titles', function (Blueprint $table) {
            $table->id();
            $table->string('tconst')->unique();
            $table->string('title_type')->nullable()->default(null)->index();
            $table->text('primary_title')->nullable()->default(null);
            $table->text('original_title');
            $table->boolean('is_adult')->default(0);
            $table->integer('start_year')->nullable()->default(null)->index();
            $table->integer('end_year')->nullable()->default(null)->index();
            $table->integer('runtime_minutes')->nullable()->default(null);
            $table->text('genres')->nullable()->default(null);
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
        Schema::dropIfExists('imdb_titles');
    }
};
