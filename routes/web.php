<?php

use Illuminate\Support\Facades\Route;

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

use App\Http\Controllers\ExcelImportController;
use App\Http\Controllers\ImportController;

Route::get('/', [ExcelImportController::class ,'home'])->name('home');
Route::post('/excelImport', [ExcelImportController::class , 'excelImport'])->name('excelImport');




Route::get('/upload', [ImportController::class , 'index']);
Route::post('/store', [ImportController::class , 'store'])->name('store');
Route::get('/progress', [ImportController::class , 'progress'])->name('progress');
Route::get('/batch/{batchId}', [ImportController::class , 'batch'])->name('batch');