<?php
require_once "Session.php";
require_once "config.php";

$data = json_decode(file_get_contents("php://input"), true);
$exercise_id = $data['exercise_id'] ?? null;
if (!$exercise_id) exit;

$pdo->prepare("DELETE FROM exercises WHERE Exercise_Id=?")->execute([$exercise_id]);
$pdo->prepare("DELETE FROM class_exercises WHERE exercise_id=?")->execute([$exercise_id]);
$pdo->prepare("DELETE FROM exercise_questions WHERE Exercise_Id=?")->execute([$exercise_id]);
