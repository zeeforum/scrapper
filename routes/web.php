<?php

use App\Http\Controllers\ScrapperController;
use App\Http\Controllers\Scrappers\IndeedController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::controller(IndeedController::class)->group(function() {
    Route::get('/scrapper', 'search');
    Route::get('/scrapper/job/{jobId}', 'getJobDetail');
});

Route::get('/', function () {
    return view('welcome');
});


Route::get('/critical', function() {
    Log::critical("Sample message!");
    return "Log sent to slack";
});

Route::get('/peer', function () {
    return view('peer-chat');
});