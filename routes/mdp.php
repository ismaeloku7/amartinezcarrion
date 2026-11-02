<?php

use App\Livewire\Mdp;
use Illuminate\Support\Facades\Route;

Route::prefix('mdp')->name('mdp.')->middleware(['auth', 'access:mdp'])->group(function () {
    Route::get('/empezar', Mdp\Onboarding::class)->name('onboarding');

    Route::middleware('mdp.enrolled')->group(function () {
        Route::get('/', Mdp\Today::class)->name('today');
        Route::get('/devocional/{date?}', Mdp\Devotional::class)->name('devotional');
        Route::get('/semana', Mdp\WeekCard::class)->name('week');
        Route::get('/descanso', Mdp\RestDay::class)->name('rest');
        Route::get('/mes/{month?}', Mdp\MonthClose::class)->name('month');
        Route::get('/galeria', Mdp\YearGallery::class)->name('year');
        Route::get('/libreta', Mdp\History::class)->name('history');
        Route::get('/lectura', Mdp\ReadingOrder::class)->name('order');
        Route::get('/ajustes', Mdp\Settings::class)->name('settings');
    });
});
