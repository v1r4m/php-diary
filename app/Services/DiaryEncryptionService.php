<?php

namespace App\Services;

use Exception;

/**
 * Diary Encryption Service
 *
 * This service implements client-side encryption for diary content.
 * The encryption key is derived from the user's password using PBKDF2,
 * which means:
 * 1. The server NEVER has access to the encryption key
 * 2. Only the user who knows their password can decrypt their diaries
 * 3. Even database administrators cannot read diary content
 * 4. If the user forgets their password, diaries are PERMANENTLY LOST
 *
 * Security Implementation:
 * - AES-256-GCM encryption (authenticated encryption)
 * - PBKDF2 key derivation with 100,000 iterations
 * - Random salt per diary entry
 * - Random IV (Initialization Vector) per diary entry
 * - User password is NEVER stored or transmitted in plain text
 */
class DiaryEncryptionService
{
    private const CIPHER_METHOD = 'aes-256-gcm';
    private const KEY_LENGTH = 32; // 256 bits
    private const PBKDF2_ITERATIONS = 100000;
    private const PBKDF2_ALGORITHM = 'sha256';

    /**
     * Derive encryption key from user password
     * This is done on the client side ideally, but shown here for demonstration
     *
     * @param string $password User's plain password
     * @param string $salt Random salt (base64 encoded)
     * @return string Derived key (raw binary)
     */
    public function deriveKey(string $password, string $salt): string
    {
        $saltBinary = base64_decode($salt);

        return hash_pbkdf2(
            self::PBKDF2_ALGORITHM,
            $password,
            $saltBinary,
            self::PBKDF2_ITERATIONS,
            self::KEY_LENGTH,
            true // Return raw binary
        );
    }

    /**
     * Generate random salt for key derivation
     *
     * @return string Base64 encoded salt
     */
    public function generateSalt(): string
    {
        return base64_encode(random_bytes(32));
    }

    /**
     * Generate random IV for encryption
     *
     * @return string Base64 encoded IV
     */
    public function generateIV(): string
    {
        $ivLength = openssl_cipher_iv_length(self::CIPHER_METHOD);
        return base64_encode(random_bytes($ivLength));
    }

    /**
     * Encrypt diary content
     *
     * @param string $content Plain text content
     * @param string $password User's password (used to derive key)
     * @param string $salt Salt for key derivation
     * @param string $iv Initialization vector
     * @return array ['encrypted' => string, 'tag' => string] Base64 encoded
     * @throws Exception
     */
    public function encrypt(string $content, string $password, string $salt, string $iv): array
    {
        $key = $this->deriveKey($password, $salt);
        $ivBinary = base64_decode($iv);

        $tag = '';
        $encrypted = openssl_encrypt(
            $content,
            self::CIPHER_METHOD,
            $key,
            OPENSSL_RAW_DATA,
            $ivBinary,
            $tag
        );

        if ($encrypted === false) {
            throw new Exception('Encryption failed');
        }

        return [
            'encrypted' => base64_encode($encrypted),
            'tag' => base64_encode($tag)
        ];
    }

    /**
     * Decrypt diary content
     *
     * @param string $encryptedContent Base64 encoded encrypted content
     * @param string $tag Base64 encoded authentication tag
     * @param string $password User's password
     * @param string $salt Salt used for key derivation
     * @param string $iv Initialization vector used for encryption
     * @return string Plain text content
     * @throws Exception
     */
    public function decrypt(
        string $encryptedContent,
        string $tag,
        string $password,
        string $salt,
        string $iv
    ): string {
        $key = $this->deriveKey($password, $salt);
        $ivBinary = base64_decode($iv);
        $encryptedBinary = base64_decode($encryptedContent);
        $tagBinary = base64_decode($tag);

        $decrypted = openssl_decrypt(
            $encryptedBinary,
            self::CIPHER_METHOD,
            $key,
            OPENSSL_RAW_DATA,
            $ivBinary,
            $tagBinary
        );

        if ($decrypted === false) {
            throw new Exception('Decryption failed - wrong password or corrupted data');
        }

        return $decrypted;
    }

    /**
     * Encrypt diary title (same method as content)
     *
     * @param string $title Plain text title
     * @param string $password User's password
     * @param string $salt Salt for key derivation
     * @param string $iv Initialization vector
     * @return array ['encrypted' => string, 'tag' => string]
     */
    public function encryptTitle(string $title, string $password, string $salt, string $iv): array
    {
        return $this->encrypt($title, $password, $salt, $iv);
    }

    /**
     * Decrypt diary title
     *
     * @param string $encryptedTitle Base64 encoded encrypted title
     * @param string $tag Base64 encoded authentication tag
     * @param string $password User's password
     * @param string $salt Salt used for key derivation
     * @param string $iv Initialization vector
     * @return string Plain text title
     */
    public function decryptTitle(
        string $encryptedTitle,
        string $tag,
        string $password,
        string $salt,
        string $iv
    ): string {
        return $this->decrypt($encryptedTitle, $tag, $password, $salt, $iv);
    }
}
