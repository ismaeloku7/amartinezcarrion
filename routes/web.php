<?php

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\PushController;
use App\Http\Controllers\PwaController;
use App\Livewire\Portal\Account;
use App\Livewire\Portal\Home;
use App\Livewire\Portal\Paywall;
use Illuminate\Support\Facades\Route;

Route::get('/', Home::class)->name('home');
Route::get('/planes/{product}', Paywall::class)->name('paywall');

Route::get('/pwa/{app}/manifest.webmanifest', [PwaController::class, 'manifest'])
    ->where('app', 'portal|mdp|cards')->name('pwa.manifest');

Route::middleware('auth')->group(function () {
    Route::get('/cuenta', Account::class)->name('account');

    Route::post('/push/subscribe', [PushController::class, 'subscribe'])->name('push.subscribe');
    Route::post('/push/unsubscribe', [PushController::class, 'unsubscribe'])->name('push.unsubscribe');

    Route::get('/checkout/{plan}', [CheckoutController::class, 'checkout'])->name('checkout');
    Route::get('/checkout/success', [CheckoutController::class, 'success'])->name('checkout.success');
    Route::get('/checkout/cancel', [CheckoutController::class, 'cancel'])->name('checkout.cancel');
    Route::get('/billing', [CheckoutController::class, 'portal'])->name('billing.portal');
});

require __DIR__.'/mdp.php';
require __DIR__.'/cards.php';
require __DIR__.'/tests.php';
require __DIR__.'/contents.php';
