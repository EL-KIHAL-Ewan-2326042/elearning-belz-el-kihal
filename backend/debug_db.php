<?php
$dsn = 'mysql:host=mysql-chtiouimaxxing.alwaysdata.net;dbname=chtiouimaxxing_db;charset=utf8mb4';
$user = 'chtiouimaxxing';
$pass = 'nHe89PTjlZRdswdd';

echo "Connecting to DB...\n";

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "COURSES:\n";
    $stmt3 = $pdo->query("SELECT id, title, teacher_id FROM course");
    $courses = $stmt3->fetchAll(PDO::FETCH_ASSOC);
    print_r($courses);
    
    echo "USERS:\n";
    $stmt = $pdo->query("SELECT id, email, user_type, roles FROM user ORDER BY id DESC LIMIT 5");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($users);
    
    echo "QUIZ ATTEMPTS:\n";
    $stmt2 = $pdo->query("SELECT id, student_id, score, submitted_at FROM quiz_attempt ORDER BY id DESC LIMIT 5");
    $attempts = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    print_r($attempts);

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
