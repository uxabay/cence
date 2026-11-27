<?php

use App\Http\Controllers\ContractOverviewPdfController;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect('/admin'))->name('home');

Route::get('/admin/contracts/{contract}/overview/pdf', ContractOverviewPdfController::class)
    ->middleware(Authenticate::class)
    ->name('admin.contracts.overview.pdf');
