<?php
/**
 * Kontaktformular Handler
 * @version 2.1.0
 */

// Lade Konfiguration und Hilfsfunktionen
require_once __DIR__ . '/secure-data/config.php';

// Fehlerberichterstattung aktivieren
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', LOG_FILE);

// CORS und Sicherheitsheader
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // Logge Anfrage
    error_log("Neue Formularanfrage empfangen");
    
    // Prüfe Request-Methode
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Nur POST-Requests sind erlaubt.', 405);
    }
    
    // Lese Eingabedaten
    $rawInput = file_get_contents('php://input');
    error_log("Empfangene Daten: " . $rawInput);
    
    $input = json_decode($rawInput, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Ungültige JSON-Daten: ' . json_last_error_msg(), 400);
    }
    
    // Validiere Pflichtfelder
    if (empty($input['name']) || empty($input['email']) || empty($input['message'])) {
        throw new Exception('Alle Felder müssen ausgefüllt werden.', 400);
    }
    
    // Validiere E-Mail
    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Ungültige E-Mail-Adresse.', 400);
    }
    
    // Erstelle Nachricht
    $message = [
        'timestamp' => date('Y-m-d H:i:s'),
        'name' => htmlspecialchars($input['name']),
        'email' => $input['email'],
        'message' => htmlspecialchars($input['message']),
        'ip' => $_SERVER['REMOTE_ADDR']
    ];
    
    // Verschlüssele und speichere Nachricht
    $encrypted = encrypt_data(json_encode($message));
    
    // Speichere in Datei
    $success = file_put_contents(
        MESSAGES_FILE,
        $encrypted . PHP_EOL,
        FILE_APPEND | LOCK_EX
    );
    
    if ($success === false) {
        throw new Exception('Fehler beim Speichern der Nachricht.', 500);
    }
    
    // Erfolgreiche Antwort
    echo json_encode([
        'success' => true,
        'message' => 'Ihre Nachricht wurde erfolgreich gesendet.'
    ]);
    
    error_log("Nachricht erfolgreich gespeichert");
    
} catch (Exception $e) {
    error_log("Fehler: " . $e->getMessage());
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}