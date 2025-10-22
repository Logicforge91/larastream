<?php

use App\Http\Controllers\ProfileController;
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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';



use App\Http\Controllers\StreamController;
use App\Http\Controllers\GuestController;

Route::get('/', [StreamController::class, 'index']);
Route::get('/stream/{id}', [StreamController::class, 'show']);
Route::post('/stream', [StreamController::class, 'store']);
Route::post('/upload-video', [StreamController::class, 'uploadVideo']);

# Guest join
Route::get('/guest/join/{uuid}', [GuestController::class, 'join']);


use App\Http\Controllers\WebrtcController;


Route::post('/webrtc/send', [WebrtcController::class, 'send']);
Route::get('/webrtc/receive/{stream_id}/{receiver_type}', [WebrtcController::class, 'receive']);
