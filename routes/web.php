<?php

use Illuminate\Support\Facades\Route;
use App\Models\HelloWorld;
use App\Services\DiaryEncryptionService;

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

// Hello World route - Test database connection
Route::get('/', function () {
    try {
        $message = HelloWorld::first();
        return response()->json([
            'status' => 'success',
            'message' => $message ? $message->message : 'No message found',
            'database' => 'connected'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Database connection failed',
            'error' => $e->getMessage()
        ], 500);
    }
});

// Test encryption/decryption
Route::get('/test-encryption', function () {
    $encryptionService = new DiaryEncryptionService();

    // Sample data
    $password = 'user_secret_password_123';
    $content = 'This is my secret diary entry that nobody should be able to read!';
    $title = 'My Secret Day';

    // Generate salt and IV
    $salt = $encryptionService->generateSalt();
    $iv = $encryptionService->generateIV();

    // Encrypt
    $encryptedContent = $encryptionService->encrypt($content, $password, $salt, $iv);
    $encryptedTitle = $encryptionService->encryptTitle($title, $password, $salt, $iv);

    // Decrypt
    $decryptedContent = $encryptionService->decrypt(
        $encryptedContent['encrypted'],
        $encryptedContent['tag'],
        $password,
        $salt,
        $iv
    );

    $decryptedTitle = $encryptionService->decryptTitle(
        $encryptedTitle['encrypted'],
        $encryptedTitle['tag'],
        $password,
        $salt,
        $iv
    );

    // Try with wrong password
    $wrongPasswordError = null;
    try {
        $encryptionService->decrypt(
            $encryptedContent['encrypted'],
            $encryptedContent['tag'],
            'wrong_password',
            $salt,
            $iv
        );
    } catch (\Exception $e) {
        $wrongPasswordError = $e->getMessage();
    }

    return response()->json([
        'status' => 'success',
        'test' => [
            'original_title' => $title,
            'original_content' => $content,
            'encrypted_title' => $encryptedTitle['encrypted'],
            'encrypted_content' => $encryptedContent['encrypted'],
            'decrypted_title' => $decryptedTitle,
            'decrypted_content' => $decryptedContent,
            'salt' => $salt,
            'iv' => $iv,
            'wrong_password_error' => $wrongPasswordError,
            'encryption_verified' => ($decryptedContent === $content && $decryptedTitle === $title)
        ],
        'security_notes' => [
            'encryption_algorithm' => 'AES-256-GCM',
            'key_derivation' => 'PBKDF2 with 100,000 iterations',
            'admin_cannot_decrypt' => 'Encryption key is derived from user password',
            'password_never_stored' => 'Only hashed password is stored in database'
        ]
    ]);
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
            'Public/Private diary support'
        ],
        'security' => [
            'Diary content encrypted with user password',
            'Admins cannot read diary content',
            'Each diary has unique salt and IV',
            'Password recovery = data loss (by design)'
        ]
    ]);
});
