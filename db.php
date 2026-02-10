<?php
$host = 'localhost';
$dbname = 'freelance_marketplace'; 
$username = 'root'; 
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->query("SELECT title, year , pages FROM books");

// FETCH_ASSOC تحول كل سطر في الجدول إلى مصفوفة ترابطية (Key => Value)
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
    header('Content-Type: application/json');
    echo json_encode($books, JSON_NUMERIC_CHECK);
    
} catch (PDOException $e) {
    die(" Error in connecting to the database: " . $e->getMessage());
}
?>