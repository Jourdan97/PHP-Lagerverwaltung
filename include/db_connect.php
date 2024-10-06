<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$host = ''; // oder der Name deines Hosts
$db = '';
$user = ''; // setze hier den Benutzernamen deiner DB
$pass = '!'; // und hier das Passwort deiner DB

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    // Fehler im Verbindungsmodus festlegen
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Verbindung zur Datenbank fehlgeschlagen: " . $e->getMessage());
}
?>