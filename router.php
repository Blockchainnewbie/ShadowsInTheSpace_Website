<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');

// Logge alle Anfragen
$requestUri = $_SERVER['REQUEST_URI'];
error_log("Eingehende Anfrage: " . $requestUri);

// Entferne Query-String und führende/nachfolgende Slashes
$path = parse_url($requestUri, PHP_URL_PATH);
$path = trim($path, '/');
$path = $path ?: 'test-message.html'; // Standard-Route

// Bestimme den vollständigen Dateipfad
$filePath = __DIR__ . '/' . $path;
error_log("Versuche Datei zu laden: " . $filePath);

// Prüfe ob die Datei existiert
if (!file_exists($filePath)) {
    error_log("Datei nicht gefunden: " . $filePath);
    header('HTTP/1.0 404 Not Found');
    echo "404 - Datei nicht gefunden: " . htmlspecialchars($path);
    exit;
}

// Behandle PHP-Dateien
if (pathinfo($filePath, PATHINFO_EXTENSION) === 'php') {
    error_log("Verarbeite PHP-Datei: " . $filePath);
    require $filePath;
    exit;
}

// Setze den korrekten Content-Type
$mimeTypes = [
    'html' => 'text/html',
    'css'  => 'text/css',
    'js'   => 'application/javascript',
    'json' => 'application/json',
    'png'  => 'image/png',
    'jpg'  => 'image/jpeg',
    'gif'  => 'image/gif',
    'webp' => 'image/webp'
];

$ext = pathinfo($filePath, PATHINFO_EXTENSION);
if (isset($mimeTypes[$ext])) {
    header('Content-Type: ' . $mimeTypes[$ext]);
}

// Liefere die Datei aus
error_log("Liefere Datei aus: " . $filePath);
readfile($filePath);