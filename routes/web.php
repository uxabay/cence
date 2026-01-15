<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect('/admin'))->name('home');

