<?php
/**
 * Sichere Konfigurationsdatei für Verschlüsselung
 * @version 1.0.0
 * 
 * WICHTIG: Diese Datei muss außerhalb des Web-Root oder in einem
 * geschützten Verzeichnis gespeichert werden
 */

// Verschlüsselungsschlüssel
define('ENCRYPTION_KEY', '5v8y/B?E(H+MbQeThWmZq4t6w9z$C&F)');

// Dateipfade relativ zum Projektroot
define('MESSAGES_FILE', __DIR__ . '/../messages.txt');
define('LOG_FILE', __DIR__ . '/../form_errors.log');

// Verschlüsselungskonfiguration
define('CIPHER_METHOD', 'AES-256-CBC');

/**
 * Hilfsfunktion zum sicheren Verschlüsseln
 * @param string $data Die zu verschlüsselnden Daten
 * @return string Format: base64(iv):encrypted_data
 */
function encrypt_data($data) {
    $iv = openssl_random_pseudo_bytes(16);
    $encrypted = openssl_encrypt(
        $data,
        CIPHER_METHOD,
        ENCRYPTION_KEY,
        0,
        $iv
    );
    return base64_encode($iv) . ':' . $encrypted;
}

/**
 * Hilfsfunktion zum sicheren Entschlüsseln
 * @param string $encrypted_data Format: base64(iv):encrypted_data
 * @return string|false Die entschlüsselten Daten oder false bei Fehler
 */
function decrypt_data($encrypted_data) {
    list($encoded_iv, $encrypted) = explode(':', $encrypted_data);
    $iv = base64_decode($encoded_iv);
    return openssl_decrypt(
        $encrypted,
        CIPHER_METHOD,
        ENCRYPTION_KEY,
        0,
        $iv
    );
}