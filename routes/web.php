<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('Page.landing');
});

Route::get('/masuk', function () {
    return view('Page.masuk');
})->name('masuk');

Route::get('/daftar', function () {
    return view('Page.daftar');
})->name('daftar');
