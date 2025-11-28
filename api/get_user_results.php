<?php
require_once "config.php";
require_once "Session.php";

if (!isset($_SESSION['user']['id'])) {
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit;
}

$userId = $_SESSION['user']['id'];

$stmt = $pdo->prepare("
    SELECT r.Result_Id, r.Score, r.Completed, r.Completed_At,
           e.Title, e.Type, e.Exercise_Id
    FROM user_results r
    JOIN exercises e ON r.Exercise_Id = e.Exercise_Id
    WHERE r.User_Id = ?
    ORDER BY r.Completed_At DESC
");
$stmt->execute([$userId]);

$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    "success" => true,
    "results" => $results
]);
