<?php
/**
 * Nachrichten-Entschlüsselungs-Script
 * Liest verschlüsselte Nachrichten und zeigt sie entschlüsselt an
 * 
 * @version 1.1.0
 */

// Lade Konfiguration und Hilfsfunktionen
require_once __DIR__ . '/secure-data/config.php';

// HTML Header für schönere Ausgabe
echo '<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Entschlüsselte Nachrichten</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; }
        .message { border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
        .timestamp { color: #666; font-size: 0.9em; }
        .error { color: red; padding: 10px; border: 1px solid red; margin: 10px 0; }
    </style>
</head>
<body>
    <h1>Entschlüsselte Nachrichten</h1>';

try {
    // Prüfe ob die Datei existiert
    if (!file_exists(MESSAGES_FILE)) {
        throw new Exception("Die Datei messages.txt wurde nicht gefunden.");
    }

    // Lese die verschlüsselten Nachrichten
    $messages = file(MESSAGES_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    if (empty($messages)) {
        echo "<p>Keine Nachrichten gefunden.</p>";
    } else {
        // Verarbeite jede Nachricht
        foreach ($messages as $encryptedMessage) {
            try {
                // Entschlüssele die Nachricht
                $decrypted = decrypt_data($encryptedMessage);
                
                if ($decrypted === false) {
                    throw new Exception("Entschlüsselung fehlgeschlagen");
                }
                
                // Dekodiere JSON
                $messageData = json_decode($decrypted, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception("JSON Dekodierung fehlgeschlagen: " . json_last_error_msg());
                }
                
                // Zeige die Nachricht an
                echo "<div class='message'>";
                echo "<p class='timestamp'>Zeitstempel: " . htmlspecialchars($messageData['timestamp']) . "</p>";
                echo "<p><strong>Name:</strong> " . htmlspecialchars($messageData['name']) . "</p>";
                echo "<p><strong>E-Mail:</strong> " . htmlspecialchars($messageData['email']) . "</p>";
                echo "<p><strong>Nachricht:</strong><br>" . nl2br(htmlspecialchars($messageData['message'])) . "</p>";
                if (isset($messageData['ip'])) {
                    echo "<p class='timestamp'>IP: " . htmlspecialchars($messageData['ip']) . "</p>";
                }
                echo "</div>";
                
            } catch (Exception $e) {
                echo "<div class='error'>Fehler beim Verarbeiten einer Nachricht: " . htmlspecialchars($e->getMessage()) . "</div>";
            }
        }
    }
    
} catch (Exception $e) {
    echo "<div class='error'>Fehler: " . htmlspecialchars($e->getMessage()) . "</div>";
}

// HTML Footer
echo '</body></html>';