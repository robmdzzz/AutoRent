<?php
// config/conexion.php

$host = '127.0.0.1'; // O 'localhost'
$dbname = 'autorent_db';
$user = 'root';
$password = ''; // En Laragon por defecto está vacía
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

try {
    $pdo = new PDO($dsn, $user, $password);
    // Configurar PDO para que lance excepciones en caso de error
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Configurar que los resultados se devuelvan como arreglos asociativos
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // echo "¡Conexión exitosa!"; // Comentario de prueba
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>