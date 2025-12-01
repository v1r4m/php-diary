/**
 * Client-side encryption module using Web Crypto API
 *
 * Security design:
 * - All encryption/decryption happens in the browser
 * - Server never sees plaintext content or encryption keys
 * - diaryKey is derived from password using PBKDF2 and kept only in memory
 * - diary_token is a separate hash for API authentication (stored in localStorage)
 */

const DiaryEncryption = (function() {
    'use strict';

    // Constants
    const PBKDF2_ITERATIONS = 100000;
    const KEY_LENGTH = 256; // bits
    const IV_LENGTH = 12; // bytes for AES-GCM
    const SALT_LENGTH = 32; // bytes

    // In-memory storage for the derived encryption key
    // This is cleared on page refresh/close
    let _diaryKey = null;

    /**
     * Convert ArrayBuffer to Base64 string
     */
    function arrayBufferToBase64(buffer) {
        const bytes = new Uint8Array(buffer);
        let binary = '';
        for (let i = 0; i < bytes.byteLength; i++) {
            binary += String.fromCharCode(bytes[i]);
        }
        return btoa(binary);
    }

    /**
     * Convert Base64 string to ArrayBuffer
     */
    function base64ToArrayBuffer(base64) {
        const binary = atob(base64);
        const bytes = new Uint8Array(binary.length);
        for (let i = 0; i < binary.length; i++) {
            bytes[i] = binary.charCodeAt(i);
        }
        return bytes.buffer;
    }

    /**
     * Convert string to ArrayBuffer (UTF-8)
     */
    function stringToArrayBuffer(str) {
        return new TextEncoder().encode(str);
    }

    /**
     * Convert ArrayBuffer to string (UTF-8)
     */
    function arrayBufferToString(buffer) {
        return new TextDecoder().decode(buffer);
    }

    /**
     * Convert ArrayBuffer to Hex string
     */
    function arrayBufferToHex(buffer) {
        const bytes = new Uint8Array(buffer);
        return Array.from(bytes).map(b => b.toString(16).padStart(2, '0')).join('');
    }

    /**
     * Generate a random salt
     */
    function generateSalt() {
        const salt = new Uint8Array(SALT_LENGTH);
        crypto.getRandomValues(salt);
        return arrayBufferToBase64(salt.buffer);
    }

    /**
     * Generate a random IV for AES-GCM
     */
    function generateIV() {
        const iv = new Uint8Array(IV_LENGTH);
        crypto.getRandomValues(iv);
        return arrayBufferToBase64(iv.buffer);
    }

    /**
     * Derive encryption key from password using PBKDF2
     * @param {string} password - User's password
     * @param {string} salt - Base64 encoded salt
     * @returns {Promise<CryptoKey>} - AES-GCM key
     */
    async function deriveKey(password, salt) {
        // Import password as raw key material
        const keyMaterial = await crypto.subtle.importKey(
            'raw',
            stringToArrayBuffer(password),
            'PBKDF2',
            false,
            ['deriveBits', 'deriveKey']
        );

        // Derive AES-GCM key
        const key = await crypto.subtle.deriveKey(
            {
                name: 'PBKDF2',
                salt: base64ToArrayBuffer(salt),
                iterations: PBKDF2_ITERATIONS,
                hash: 'SHA-256'
            },
            keyMaterial,
            { name: 'AES-GCM', length: KEY_LENGTH },
            false, // not extractable
            ['encrypt', 'decrypt']
        );

        return key;
    }

    /**
     * Generate diary_token from password and user ID
     * This token is used for API authentication (separate from encryption key)
     * Formula: SHA-256("DIARY_TOKEN::" + password + "::" + userId)
     *
     * @param {string} password - User's password
     * @param {number} userId - User's ID
     * @returns {Promise<string>} - Hex encoded token (64 chars)
     */
    async function generateDiaryToken(password, userId) {
        const data = `DIARY_TOKEN::${password}::${userId}`;
        const hashBuffer = await crypto.subtle.digest('SHA-256', stringToArrayBuffer(data));
        return arrayBufferToHex(hashBuffer);
    }

    /**
     * Initialize encryption with password
     * Derives the encryption key and stores it in memory
     *
     * @param {string} password - User's password
     * @param {string} salt - User's encryption salt (base64)
     * @returns {Promise<boolean>} - True if successful
     */
    async function initialize(password, salt) {
        try {
            _diaryKey = await deriveKey(password, salt);
            return true;
        } catch (error) {
            console.error('Failed to initialize encryption:', error);
            return false;
        }
    }

    /**
     * Check if encryption is initialized
     */
    function isInitialized() {
        return _diaryKey !== null;
    }

    /**
     * Clear the encryption key from memory
     */
    function clear() {
        _diaryKey = null;
    }

    /**
     * Encrypt plaintext content
     *
     * @param {string} plaintext - Content to encrypt
     * @returns {Promise<{ciphertext: string, iv: string, salt: string}>}
     */
    async function encrypt(plaintext) {
        if (!_diaryKey) {
            throw new Error('Encryption not initialized. Call initialize() first.');
        }

        const iv = new Uint8Array(IV_LENGTH);
        crypto.getRandomValues(iv);

        const salt = new Uint8Array(SALT_LENGTH);
        crypto.getRandomValues(salt);

        const ciphertext = await crypto.subtle.encrypt(
            { name: 'AES-GCM', iv: iv },
            _diaryKey,
            stringToArrayBuffer(plaintext)
        );

        return {
            ciphertext: arrayBufferToBase64(ciphertext),
            iv: arrayBufferToBase64(iv.buffer),
            salt: arrayBufferToBase64(salt.buffer)
        };
    }

    /**
     * Encrypt diary entry (title + content as JSON)
     * This ensures title and content are encrypted together with a single IV
     *
     * @param {string} title - Diary title
     * @param {string} content - Diary content
     * @returns {Promise<{ciphertext: string, iv: string, salt: string}>}
     */
    async function encryptDiaryEntry(title, content) {
        const payload = JSON.stringify({ title, content });
        return encrypt(payload);
    }

    /**
     * Decrypt diary entry and return title and content
     *
     * @param {string} ciphertext - Base64 encoded ciphertext
     * @param {string} iv - Base64 encoded IV
     * @returns {Promise<{title: string, content: string}>}
     */
    async function decryptDiaryEntry(ciphertext, iv) {
        const plaintext = await decrypt(ciphertext, iv);
        return JSON.parse(plaintext);
    }

    /**
     * Decrypt ciphertext content
     *
     * @param {string} ciphertext - Base64 encoded ciphertext
     * @param {string} iv - Base64 encoded IV
     * @returns {Promise<string>} - Decrypted plaintext
     */
    async function decrypt(ciphertext, iv) {
        if (!_diaryKey) {
            throw new Error('Encryption not initialized. Call initialize() first.');
        }

        try {
            const plaintext = await crypto.subtle.decrypt(
                { name: 'AES-GCM', iv: base64ToArrayBuffer(iv) },
                _diaryKey,
                base64ToArrayBuffer(ciphertext)
            );

            return arrayBufferToString(plaintext);
        } catch (error) {
            // AES-GCM will throw if authentication fails (wrong key/tampered data)
            throw new Error('Decryption failed. Wrong password or corrupted data.');
        }
    }

    /**
     * Store diary token in localStorage
     */
    function storeDiaryToken(token) {
        localStorage.setItem('diary_token', token);
    }

    /**
     * Get diary token from localStorage
     */
    function getDiaryToken() {
        return localStorage.getItem('diary_token');
    }

    /**
     * Clear diary token from localStorage
     */
    function clearDiaryToken() {
        localStorage.removeItem('diary_token');
    }

    // Public API
    return {
        generateDiaryToken,
        generateSalt,
        generateIV,
        initialize,
        isInitialized,
        clear,
        encrypt,
        decrypt,
        encryptDiaryEntry,
        decryptDiaryEntry,
        storeDiaryToken,
        getDiaryToken,
        clearDiaryToken
    };
})();

// Make it globally available
window.DiaryEncryption = DiaryEncryption;
