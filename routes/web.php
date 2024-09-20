<?php

use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\CertificateController;

// Route::get('/certificates/generate/{participant}', [CertificateController::class, 'generate'])->name('certificates.generate');

Route::get('/', function () {
    return view('welcome');
});

Route::get('/pdf', function () {
    return view('certificates.pdf-test');
});