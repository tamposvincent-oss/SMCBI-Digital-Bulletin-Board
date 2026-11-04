<?php

$host = "sql101.infinityfree.com";
$dbname = "if0_43080149_smcbi_bulletin";
$username = "if0_43080149";
$password = "8462368123";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

?>