<?php
require_once "Session.php";
require_once "config.php";

if (!isset($_SESSION['user']['id'])) {
    echo json_encode(["success" => false, "message" => "User not logged in"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$exerciseData = $data["exercise"] ?? null;
$classes       = $data["classes"] ?? [];

if (!$exerciseData) {
    echo json_encode(["success" => false, "message" => "No exercise data"]);
    exit;
}

$exerciseId = $exerciseData["exercise_id"] ?? null;
$type       = $exerciseData["type"];
$maxXP      = intval($exerciseData["max_xp"]);

try {
    $pdo->beginTransaction();

    /* =====================================
        CREATE OR UPDATE EXERCISE
    ====================================== */

    if ($exerciseId) {
        $stmt = $pdo->prepare("
            UPDATE exercises
            SET Title=?, Description=?, Type=?, Max_XP=?
            WHERE Exercise_Id=?
        ");
        $stmt->execute([
            $exerciseData["title"],
            $exerciseData["description"],
            $type,
            $maxXP,
            $exerciseId
        ]);
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO exercises (Title, Description, Type, Created_By, Max_XP)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $exerciseData["title"],
            $exerciseData["description"],
            $type,
            $_SESSION['user']['id'],
            $maxXP
        ]);
        $exerciseId = $pdo->lastInsertId();
    }

    /* =====================================
        DELETE OLD QUESTIONS FOR ORDERING
    ====================================== */

    if ($type === "ordering") {
        $pdo->prepare("DELETE FROM exercise_questions WHERE Exercise_Id=?")->execute([$exerciseId]);
    }

    /* =====================================
       UPDATE / INSERT QUESTIONS
    ====================================== */

    $newQids = [];

    foreach ($exerciseData["questions"] as $q) {

        if ($type === "ordering") {
            // Insert each line as its own row
            $stmt = $pdo->prepare("
                INSERT INTO exercise_questions (Exercise_Id, Statement, Correct)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$exerciseId, $q["statement"], $q["correct"]]);
            $newQids[] = $pdo->lastInsertId();
            continue;
        }

        // Other question types
        $qid = $q["Question_Id"] ?? null;

        if ($qid) {
            $stmt = $pdo->prepare("
                UPDATE exercise_questions
                SET Statement=?, Correct=?
                WHERE Question_Id=? AND Exercise_Id=?
            ");
            $stmt->execute([$q["statement"], $q["correct"], $qid, $exerciseId]);
            $newQids[] = $qid;
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO exercise_questions (Exercise_Id, Statement, Correct)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$exerciseId, $q["statement"], $q["correct"]]);
            $qid = $pdo->lastInsertId();
            $newQids[] = $qid;
        }

        /* ==========================
              MCQ OPTIONS
        ========================== */
        if ($type === "mcq") {
            $stmtOld = $pdo->prepare("SELECT Option_Id FROM question_options WHERE Question_Id=?");
            $stmtOld->execute([$qid]);
            $oldOptIds = $stmtOld->fetchAll(PDO::FETCH_COLUMN);

            $newOptIds = [];

            foreach ($q["options"] as $opt) {
                $oid = $opt["Option_Id"] ?? null;

                if ($oid) {
                    $stmt = $pdo->prepare("
                        UPDATE question_options
                        SET Option_Text=?, Is_Correct=?
                        WHERE Option_Id=? AND Question_Id=?
                    ");
                    $stmt->execute([$opt["text"], $opt["correct"], $oid, $qid]);
                    $newOptIds[] = $oid;
                } else {
                    $stmt = $pdo->prepare("
                        INSERT INTO question_options (Question_Id, Option_Text, Is_Correct)
                        VALUES (?, ?, ?)
                    ");
                    $stmt->execute([$qid, $opt["text"], $opt["correct"]]);
                    $newOptIds[] = $pdo->lastInsertId();
                }
            }

            foreach ($oldOptIds as $old) {
                if (!in_array($old, $newOptIds)) {
                    $pdo->prepare("DELETE FROM question_options WHERE Option_Id=?")->execute([$old]);
                }
            }
        }
    }

    /* =============================
         UPDATE CLASS RELATIONS
    ============================== */
    $pdo->prepare("DELETE FROM class_exercises WHERE exercise_id=?")->execute([$exerciseId]);

    foreach ($classes as $cid) {
        $pdo->prepare("
            INSERT INTO class_exercises (class_id, exercise_id)
            VALUES (?, ?)
        ")->execute([$cid, $exerciseId]);
    }

    $pdo->commit();

    echo json_encode(["success" => true, "exerciseId" => $exerciseId]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
