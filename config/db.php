<?php
// Configurações do banco de dados
$host = 'localhost';
$db = 'library_db';
$user = 'root'; 
$pass = ''; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Erro de conexão com o banco: " . $e->getMessage());
    die("Erro de conexão com o banco de dados. Tente novamente mais tarde.");
}
?>