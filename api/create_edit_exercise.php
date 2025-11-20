<?php
require_once "Session.php";
require_once "config.php";

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$exerciseData = $data["exercise"] ?? null;
$classes = $data["classes"] ?? [];

if (!$exerciseData) {
    echo json_encode(["success" => false, "message" => "Inga data skickades"]);
    exit;
}

$exerciseId = $exerciseData["exercise_id"] ?? null;

if (!isset($exerciseData["questions"]) || !is_array($exerciseData["questions"])) {
    $exerciseData["questions"] = [];
}

try {
    if ($exerciseId) {
        // --- Editera ---
        $stmt = $pdo->prepare("UPDATE exercises SET Title=?, Description=?, Type=? WHERE Exercise_Id=?");
        $stmt->execute([$exerciseData["title"], $exerciseData["description"], $exerciseData["type"], $exerciseId]);

        // Ta bort gamla frågor och options
        $pdo->prepare("DELETE FROM question_options WHERE Question_Id IN (SELECT Question_Id FROM exercise_questions WHERE Exercise_Id=?)")->execute([$exerciseId]);
        $pdo->prepare("DELETE FROM exercise_questions WHERE Exercise_Id=?")->execute([$exerciseId]);
    } else {
        // --- Skapa ny ---
        $stmt = $pdo->prepare("INSERT INTO exercises (Title, Description, Type, Created_By) VALUES (?, ?, ?, ?)");
        $stmt->execute([$exerciseData["title"], $exerciseData["description"], $exerciseData["type"], $_SESSION['user']['id']]);
        $exerciseId = $pdo->lastInsertId();
    }

    // --- Lägg till frågor ---
    foreach ($exerciseData["questions"] as $q) {
        $stmtQ = $pdo->prepare("INSERT INTO exercise_questions (Exercise_Id, Statement, Correct) VALUES (?, ?, ?)");
        $stmtQ->execute([$exerciseId, $q["statement"], $q["correct"] ?? null]);
        $questionId = $pdo->lastInsertId();

        if (isset($q["options"])) {
            foreach ($q["options"] as $opt) {
                $stmtO = $pdo->prepare("INSERT INTO question_options (Question_Id, Option_Text, Is_Correct) VALUES (?, ?, ?)");
                $stmtO->execute([$questionId, $opt["text"], $opt["correct"] ?? 0]);
            }
        }
    }

    // --- Tilldela klasser ---
    $pdo->prepare("DELETE FROM class_exercises WHERE exercise_id=?")->execute([$exerciseId]);
    foreach ($classes as $classId) {
        $stmtCE = $pdo->prepare("INSERT INTO class_exercises (class_id, exercise_id) VALUES (?, ?)");
        $stmtCE->execute([$classId, $exerciseId]);
    }

    echo json_encode(["success" => true, "exerciseId" => $exerciseId]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage(), "payload" => $data]);
}
