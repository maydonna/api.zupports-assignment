<?php

use App\Http\Controllers\RestaurantController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/test-api', function () {
    return 'Hello, this is a test API endpoint.';
});

Route::get('/restaurants', [RestaurantController::class, 'index'])
    ->name('restaurants.index');
