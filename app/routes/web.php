<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProductController;
use App\Http\Controllers\SearchController;



Route::view('/add-product', 'add-product'); // GET for form page
Route::post('/add-product', [ProductController::class, 'store']); // POST to save
//Route::post('/add-product', [ProductController::class, 'store']);
//Route::view('/search', 'search');
Route::get('/search', [SearchController::class, 'search']);

