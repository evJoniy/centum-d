<?php

use App\Http\Controllers\LinkController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LinkController::class, 'form'])->name('link.form');
Route::post('/shorten', [LinkController::class, 'shorten'])->name('link.shorten');
Route::get('/{shortenedUrl}', [LinkController::class, 'redirect'])->name('link.redirect');
