<?php

use App\Mail\WelcomeMail;
use Illuminate\Support\Facades\Mail;
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

Route::get('/', function () {
    return view('welcome');
});

// Routes for mailing
Route::get('/email', function (){
    $userDetails = [
        'name'  => 'Adurotimi Joshua'
    ];
    Mail::to('adurotimijoshua@gmail.com')->send(new WelcomeMail($userDetails));

   return new WelcomeMail($userDetails);
});
