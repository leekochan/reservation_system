<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/Reservation');
});

Route::get('/Reservation', function () {
    return view('user-dashboard');
});

Route::get('/Reservation/Admin', function () {
    return view('dashboard');
});

Route::get('/facilities', function () {
    return view('user-facilities');
});
