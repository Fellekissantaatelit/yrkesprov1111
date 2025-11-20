<?php
require_once "Session.php";
require_once "config.php";

$data = json_decode(file_get_contents("php://input"), true);
$classId = $data['class_id'];
$exerciseId = $data['exercise_id'];

$stmt = $pdo->prepare("INSERT INTO class_exercises (class_id, exercise_id) VALUES (?, ?)");
$stmt->execute([$classId, $exerciseId]);

echo json_encode(["success" => true]);
