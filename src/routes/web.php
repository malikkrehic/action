<?php

use Illuminate\Support\Facades\Route;
use MK\Action\Http\Controllers\ActionController;

Route::prefix('actions')->group(function () {
    Route::get('/', [ActionController::class, 'index'])->name('actions.index');
    Route::post('/', [ActionController::class, 'handle'])->name('actions.handle');
});
