<?php
require_once "config.php";
require_once "Session.php";

if (!isset($_SESSION['user']['id'])) {
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit;
}

$userId = $_SESSION['user']['id'];

try {

    // 1. Total XP
    $stmt = $pdo->prepare("SELECT xp FROM users WHERE u_id = ?");
    $stmt->execute([$userId]);
    $totalXP = $stmt->fetchColumn() ?? 0;


    // 2. Senaste resultat (visar även misslyckade)
    $stmt = $pdo->prepare("
        SELECT 
            ur.Exercise_Id AS exercise_id,
            ur.Score AS xp,
            ur.Completed AS completed,
            ur.Completed_At AS completed_at,
            e.Title AS title
        FROM user_results ur
        JOIN exercises e ON ur.Exercise_Id = e.Exercise_Id
        WHERE ur.User_Id = ?
        ORDER BY ur.Completed_At DESC
        LIMIT 5
    ");
    $stmt->execute([$userId]);
    $recent = $stmt->fetchAll(PDO::FETCH_ASSOC);


    // 3. Tillgängliga övningar
    //    FIX: Övning är completed om det NÅGONSIN funnits ett completed-försök.
    $stmt = $pdo->prepare("
        SELECT 
            e.Exercise_Id,
            e.Title,
            e.Description,
            e.Type,
            ce.class_id,
            (
                SELECT 
                    CASE 
                        WHEN EXISTS (
                            SELECT 1 
                            FROM user_results 
                            WHERE User_Id = ? 
                              AND Exercise_Id = e.Exercise_Id
                              AND Completed = 1
                        )
                        THEN 1 
                        ELSE 0 
                    END
            ) AS Completed
        FROM class_exercises ce
        JOIN exercises e ON ce.exercise_id = e.Exercise_Id
        JOIN users u ON ce.class_id = u.class_id
        WHERE u.u_id = ?
        ORDER BY e.Exercise_Id ASC
    ");
    $stmt->execute([$userId, $userId]);
    $available = $stmt->fetchAll(PDO::FETCH_ASSOC);


    // 4. XP Levels
    $stmt = $pdo->query("
        SELECT Level_Id, Level_Name, XP_Required 
        FROM experience_levels
        ORDER BY XP_Required ASC
    ");
    $levels = $stmt->fetchAll(PDO::FETCH_ASSOC);


    echo json_encode([
        "success" => true,
        "total_xp" => (int)$totalXP,
        "recent_results" => $recent,
        "available_exercises" => $available,
        "levels" => $levels
    ]);

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
