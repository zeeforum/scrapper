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
        Schema::create('posted_jobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('job_id')->unique();
            $table->string('job_web')->default('indeed');
            $table->string('title');
            $table->string('company_name')->nullable()->default(null);
            $table->string('location')->nullable()->default(null);
            $table->string('country', 20)->nullable()->default(null);
            $table->text('description')->nullable()->default(null);
            $table->string('total_reviews', 80)->nullable()->default(null);
            $table->string('rating', 20)->nullable()->default(null);
            $table->string('salary', 80)->nullable()->default(null);
            $table->string('job_timing', 80)->nullable()->default(null);
            $table->string('hiring')->nullable()->default(null);
            $table->string('hiring_insight')->nullable()->default(null);
            $table->string('time_posted')->nullable()->default(null);
            $table->tinyInteger('is_scrapped')->nullable()->default(0);
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
        Schema::table('posted_jobs', function (Blueprint $table) {
            //
        });
    }
};
