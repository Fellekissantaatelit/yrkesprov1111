<?php
require_once "Session.php";
require_once "config.php";

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

header("Content-Type: application/json");

// Kontrollera inloggning
if (!isset($_SESSION['user']['id'])) {
    echo json_encode(["success" => false, "message" => "User not logged in"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$exercise_id = $data['exercise_id'] ?? null;

if (!$exercise_id) {
    echo json_encode(["success" => false, "message" => "No exercise_id provided"]);
    exit;
}

try {
    $pdo->beginTransaction();

    // Ta bort options
    $pdo->prepare("
        DELETE FROM question_options 
        WHERE Question_Id IN (
            SELECT Question_Id FROM exercise_questions WHERE Exercise_Id=?
        )
    ")->execute([$exercise_id]);

    // Ta bort frågor
    $pdo->prepare("DELETE FROM exercise_questions WHERE Exercise_Id=?")
        ->execute([$exercise_id]);

    // Ta bort kopplingar till klasser
    $pdo->prepare("DELETE FROM class_exercises WHERE exercise_id=?")
        ->execute([$exercise_id]);

    // Ta bort själva övningen
    $pdo->prepare("DELETE FROM exercises WHERE Exercise_Id=?")
        ->execute([$exercise_id]);

    $pdo->commit();

    echo json_encode(["success" => true, "message" => "Övning borttagen!"]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(["success" => false, "message" => "Fel vid borttagning: " . $e->getMessage()]);
}
