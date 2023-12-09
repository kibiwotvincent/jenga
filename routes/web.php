<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountInquiryController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/search', [AccountInquiryController::class, 'search']);
Route::get('/balance', [AccountInquiryController::class, 'balance']);
Route::get('/send', [AccountInquiryController::class, 'send']);
Route::post('/equity/callback', [AccountInquiryController::class, 'callback']);
Route::get('/equity/callback', [AccountInquiryController::class, 'callback']);
