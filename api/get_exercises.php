<?php
require_once "Session.php";
require_once "config.php";

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");

if (!isset($_SESSION['user'])) {
    echo json_encode(["success" => false, "message" => "Inte inloggad"]);
    exit;
}

$userId = $_SESSION['user']['id'];
$role = $_SESSION['user']['role'];

if ($role == 1) {
    // Student: hämta övningar som är tilldelade till deras klass
    $stmt = $pdo->prepare("
        SELECT e.*
        FROM exercises e
        JOIN class_exercises ce ON e.Exercise_Id = ce.exercise_id
        JOIN users u ON ce.class_id = u.class_id
        WHERE u.u_id = ?
    ");
    $stmt->execute([$userId]);
    $exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);

} elseif ($role == 2 || $role == 3) {
    // Teacher/Admin: hämta mall-övningar + egna skapade övningar
    $stmt = $pdo->prepare("
        SELECT *
        FROM exercises
        WHERE Is_Template = 1 OR Created_By = ?
    ");
    $stmt->execute([$userId]);
    $exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    echo json_encode(["success" => false, "message" => "Fel roll"]);
    exit;
}

echo json_encode(["success" => true, "exercises" => $exercises]);
