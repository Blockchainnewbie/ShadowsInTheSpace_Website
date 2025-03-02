<?php
/**
 * @class DataStorage
 * @version 2.0.1
 * @package SecureDataStorage
 * 
 * 🛡️ Implementiert GDPR-konforme Datenspeicherung
 */
class DataStorage {
    private const STORAGE_FILE = __DIR__ . '/../messages.txt';
    private const MAX_FILE_SIZE = 5242880; // 5MB
    
    /**
     * @method saveMessage
     * @param array $message Assoziatives Array mit keys: name, email, message
     * @return void
     * @throws StorageException Bei Fehlern
     */
    public static function saveMessage(array $message): void {
        // Validierung
        if (!isset($message['name'], $message['email'], $message['message'])) {
            throw new StorageException("Ungültiges Nachrichtenformat");
        }
        
        // Verschlüsselung
        $encrypted = SecurityHandler::encryptData(json_encode($message));
        
        // Dateizugriff mit exklusiver Sperre
        $file = fopen(self::STORAGE_FILE, 'a+');
        if (!$file) {
            throw new StorageException("Dateizugriff fehlgeschlagen");
        }
        
        if (flock($file, LOCK_EX)) {
            // Größenprüfung
            if (filesize(self::STORAGE_FILE) > self::MAX_FILE_SIZE) {
                flock($file, LOCK_UN);
                fclose($file);
                throw new StorageException("Maximale Speichergröße erreicht");
            }
            
            fwrite($file, $encrypted . PHP_EOL);
            flock($file, LOCK_UN);
            fclose($file);
        } else {
            fclose($file);
            throw new StorageException("Dateisperre fehlgeschlagen");
        }
    }

    /**
     * @method readMessages
     * @return array Liste entschlüsselter Nachrichten
     * @throws StorageException Bei Fehlern
     */
    public static function readMessages(): array {
        if (!file_exists(self::STORAGE_FILE)) {
            return [];
        }
        
        $messages = [];
        $lines = file(self::STORAGE_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            try {
                $decrypted = SecurityHandler::decryptData($line);
                $messages[] = json_decode($decrypted, true);
            } catch (DecryptionException $e) {
                error_log("Dekodierungsfehler: " . $e->getMessage());
                continue;
            }
        }
        
        return $messages;
    }
}

class StorageException extends Exception {}