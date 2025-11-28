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

try {

    if ($role == 1) {

        // ============================
        // STUDENT → Endast klassens övningar
        // ============================
        $stmt = $pdo->prepare("
            SELECT e.*
            FROM exercises e
            JOIN class_exercises ce ON e.Exercise_Id = ce.exercise_id
            JOIN users u ON ce.class_id = u.class_id
            WHERE u.u_id = ?
        ");

        $stmt->execute([$userId]);
        $exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            "success" => true,
            "type" => "student",
            "exercises" => $exercises
        ]);
        exit;
    }

    // ============================
    // TEACHER / ADMIN
    // ============================

    // 1. Malluppgifter (Is_Template = 1)
    $stmtTemplates = $pdo->query("
        SELECT *
        FROM exercises
        WHERE Is_Template = 1
        ORDER BY Exercise_Id DESC
    ");
    $templates = $stmtTemplates->fetchAll(PDO::FETCH_ASSOC);

    // 2. Egna uppgifter (Created_By = userId och inte template)
    $stmtOwn = $pdo->prepare("
        SELECT *
        FROM exercises
        WHERE Is_Template = 0
        AND Created_By = ?
        ORDER BY Exercise_Id DESC
    ");
    $stmtOwn->execute([$userId]);
    $ownExercises = $stmtOwn->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "type" => "teacher",
        "templates" => $templates,
        "own_exercises" => $ownExercises
    ]);

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
