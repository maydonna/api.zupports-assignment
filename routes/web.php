<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return 'Laravel 12 - API server working correctly. Please visit <a href="'.config('app.frontend_url').'">'.config('app.frontend_url').'</a> for the frontend.';
});
