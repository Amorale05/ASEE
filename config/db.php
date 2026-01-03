<?php
$host = 'localhost';
$db   = 'asee';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$conn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>