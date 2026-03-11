<?php
$host = 'localhost';
$user = 'root';
$pass = 'Oluwatosin25#';

try {
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS oja_inventory");
    $pdo->exec("USE oja_inventory");
    
    // Read and execute database.sql
    $sql = file_get_contents('database.sql');
    $pdo->exec($sql);
    
    echo "Database setup successful!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
