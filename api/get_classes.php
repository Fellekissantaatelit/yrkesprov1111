<?php
require_once "Session.php";
require_once "config.php";

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");

$teacherId = $_SESSION['user']['id'];

// Hämta alla klasser som läraren är kopplad till
$stmt = $pdo->prepare("
    SELECT c.class_id, c.class_name
    FROM class c
    JOIN teacher_classes tc ON c.class_id = tc.class_id
    WHERE tc.teacher_id = ?
");
$stmt->execute([$teacherId]);
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    "success" => true,
    "classes" => $classes
]);
