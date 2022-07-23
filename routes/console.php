<?php

use App\Http\Controllers\Commands\Scrapper\Indeed;
use App\Models\IndeedJob;
use App\Models\PostedJob;
use App\Services\ImdbImporter;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('indeed:jobdetail', function() {
    return Indeed::fetchJobDetail();
})->purpose('Fetch Job Detail from Indeed and Save in Database');


// Import IMDB Data
Artisan::command('imdb:import.title.basics', function() {
    return ImdbImporter::readFile('title.basics.tsv');
});

Artisan::command('imdb:import.name.basics', function() {
    return ImdbImporter::readFile('name.basics.tsv', 'name.basics');
});

Artisan::command('imdb:import.title.episodes', function() {
    return ImdbImporter::readFile('title.episode.tsv');
});