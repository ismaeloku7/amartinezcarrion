<?php

use App\Livewire\Contents;
use Illuminate\Support\Facades\Route;

Route::prefix('materiales')->name('contents.')->group(function () {
    Route::get('/', Contents\Index::class)->name('index');
    Route::get('/tema/{topic:slug}', Contents\Topic::class)->name('topic');
    Route::get('/{content:slug}', Contents\Show::class)->name('show');
});
