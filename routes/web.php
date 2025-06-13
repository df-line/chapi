<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::get('/up', fn () => abort(404));
Route::get('/sanctum/csrf-cookie', fn () => abort(404));

Route::prefix('storage')->group(function () {
    Route::any('/{any?}', function () {
        abort(404);
    })->where('any', '.*');
});
