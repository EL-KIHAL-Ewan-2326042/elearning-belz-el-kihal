<?php
$dsn = 'mysql:host=mysql-chtiouimaxxing.alwaysdata.net;dbname=chtiouimaxxing_db;charset=utf8mb4';
$user = 'chtiouimaxxing';
$pass = 'nHe89PTjlZRdswdd';

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Fix Students
    $count = $pdo->exec("UPDATE user SET user_type = 'student' WHERE roles LIKE '%ROLE_STUDENT%' AND (user_type IS NULL OR user_type != 'student')");
    echo "Updated $count students.\n";

    // Fix Teachers
    $count2 = $pdo->exec("UPDATE user SET user_type = 'teacher' WHERE roles LIKE '%ROLE_TEACHER%' AND (user_type IS NULL OR user_type != 'teacher')");
    echo "Updated $count2 teachers.\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
