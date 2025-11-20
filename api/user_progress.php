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

// Hämta totalpoäng (XP)
$stmt = $pdo->prepare("SELECT xp FROM users WHERE u_id = ?");
$stmt->execute([$userId]);
$XP = $stmt->fetchColumn(); // returnerar bara xp-värdet

// Hämta senaste resultaten (t.ex. de 5 senaste)
$stmt = $pdo->prepare("SELECT r.Score, r.Completed_At, e.Title 
                       FROM user_results r
                       JOIN exercises e ON r.Exercise_Id = e.Exercise_Id
                       WHERE r.User_Id = ?
                       ORDER BY r.Completed_At DESC
                       LIMIT 5");
$stmt->execute([$userId]);
$recentResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hämta achievements
$stmt = $pdo->prepare("SELECT a.Title, a.Description, a.Icon
                       FROM user_achievements ua
                       JOIN achievements a ON ua.Achv_Id = a.Achv_Id
                       WHERE ua.User_Id = ?");
$stmt->execute([$userId]);
$achievements = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    "success" => true,
    "XP" => $XP,
    "recentResults" => $recentResults,
    "achievements" => $achievements
]);
