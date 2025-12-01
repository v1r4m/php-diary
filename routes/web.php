<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DiaryController;
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

// Home - redirect to diary or login
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('diary.index');
    }
    return redirect()->route('login');
});

// Guest routes (login/register)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/me', [AuthController::class, 'me'])->name('me');
    Route::post('/diary-token', [AuthController::class, 'storeDiaryToken'])->name('diary.token.store');

    // Diary page (view)
    Route::get('/diary', [DiaryController::class, 'index'])->name('diary.index');

    // Diary API routes (require both auth session AND diary_token)
    Route::middleware('diary.token')->prefix('api/diary')->group(function () {
        Route::get('/', [DiaryController::class, 'list'])->name('diary.list');
        Route::get('/{id}', [DiaryController::class, 'show'])->name('diary.show');
        Route::post('/', [DiaryController::class, 'store'])->name('diary.store');
        Route::put('/{id}', [DiaryController::class, 'update'])->name('diary.update');
        Route::delete('/{id}', [DiaryController::class, 'destroy'])->name('diary.destroy');
    });
});

// API Info
Route::get('/api/info', function () {
    return response()->json([
        'service' => 'Encrypted Diary Service',
        'version' => '1.0.0',
        'features' => [
            'End-to-end encryption',
            'Client-side key derivation',
            'AES-256-GCM encryption',
            'Zero-knowledge architecture'
        ],
        'security' => [
            'Diary content encrypted with user password',
            'Admins cannot read diary content',
            'Each diary has unique salt and IV',
            'Password recovery = data loss (by design)',
            'Dual authentication: session + diary_token'
        ]
    ]);
});
