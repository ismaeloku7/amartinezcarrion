<?php

use App\Livewire\Tests;
use Illuminate\Support\Facades\Route;

Route::get('/j/{token}', Tests\Johari\Respond::class)->name('johari.respond');

Route::prefix('tests')->name('tests.')->middleware('auth')->group(function () {
    Route::get('/', Tests\Index::class)->name('index');
    Route::get('/{test:key}', Tests\Show::class)->name('show');
    Route::get('/johari/{take}/palabras', Tests\Johari\SelfSelect::class)->name('johari.self');
    Route::get('/johari/{take}/invitar', Tests\Johari\Invite::class)->name('johari.invite');
    Route::get('/johari/{take}/resultados', Tests\Johari\Results::class)->name('johari.results');
    Route::get('/{test:key}/evolucion', Tests\Johari\Evolution::class)->name('johari.evolution');
});
