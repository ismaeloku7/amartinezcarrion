<?php

use App\Livewire\Cards;
use Illuminate\Support\Facades\Route;

Route::prefix('fichas')->name('cards.')->middleware(['auth', 'access:cards'])->group(function () {
    Route::get('/empezar', Cards\Onboarding::class)->name('onboarding');

    Route::middleware('cards.enrolled')->group(function () {
        Route::get('/', Cards\Today::class)->name('today');
        Route::get('/repaso', Cards\Review::class)->name('review');
        Route::get('/ficha-del-dia', Cards\DayCard::class)->name('day');
        Route::get('/caja', Cards\Box::class)->name('box');
        Route::get('/ficha/{card:number}', Cards\Show::class)->name('show');
        Route::get('/planilla/{year?}/{month?}', Cards\Tracking::class)->name('tracking');
        Route::get('/progreso', Cards\Progress::class)->name('progress');
        Route::get('/ajustes', Cards\Settings::class)->name('settings');
    });
});
