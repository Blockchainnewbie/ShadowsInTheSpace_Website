<?php
/**
 * @class SecurityHandler
 * @version 1.2.0
 * @package DataSecurity
 * 
 * 🔒 Neue Sicherheitsimplementierung gemäß PCI-DSS Standard
 */
class SecurityHandler {
    private const CIPHER_METHOD = 'AES-256-CBC';
    private const HASH_ALGO = 'sha3-512';
    
    /**
     * @method encryptData
     * @param string $data Zu verschlüsselnder Text
     * @return string Base64-kodierter verschlüsselter Text
     * @throws EncryptionException Bei Fehlern
     */
    public static function encryptData(string $data): string {
        $iv_length = openssl_cipher_iv_length(self::CIPHER_METHOD);
        $iv = openssl_random_pseudo_bytes($iv_length);
        $key = getenv('SECURITY_KEY');
        
        $encrypted = openssl_encrypt(
            $data,
            self::CIPHER_METHOD,
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );
        
        if ($encrypted === false) {
            throw new EncryptionException(
                "Verschlüsselungsfehler: " . openssl_error_string()
            );
        }
        
        return base64_encode($iv . $encrypted);
    }

    /**
     * @method decryptData
     * @param string $encrypted_data Verschlüsselter Text
     * @return string Entschlüsselter Klartext
     * @throws DecryptionException Bei Fehlern
     */
    public static function decryptData(string $encrypted_data): string {
        $decoded = base64_decode($encrypted_data);
        $iv_length = openssl_cipher_iv_length(self::CIPHER_METHOD);
        
        $iv = substr($decoded, 0, $iv_length);
        $encrypted = substr($decoded, $iv_length);
        $key = getenv('SECURITY_KEY');
        
        $data = openssl_decrypt(
            $encrypted,
            self::CIPHER_METHOD,
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );
        
        if ($data === false) {
            throw new DecryptionException(
                "Entschlüsselungsfehler: " . openssl_error_string()
            );
        }
        
        return $data;
    }
}

class EncryptionException extends Exception {}
class DecryptionException extends Exception {}