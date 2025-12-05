<?php
require_once "Session.php";
require_once "config.php";

$exerciseId = $_GET['id'] ?? null;

if (!$exerciseId) {
    echo json_encode(["success" => false, "message" => "No exercise ID provided"]);
    exit;
}

try {

    /* ================================
         HÄMTA ÖVNING
    ================================= */
    $stmt = $pdo->prepare("SELECT * FROM exercises WHERE Exercise_Id=?");
    $stmt->execute([$exerciseId]);
    $exercise = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$exercise) {
        throw new Exception("Exercise not found");
    }

    /* ================================
          HÄMTA FRÅGOR
    ================================= */
    $stmtQ = $pdo->prepare("
        SELECT * 
        FROM exercise_questions 
        WHERE Exercise_Id=? 
        ORDER BY Correct ASC
    ");
    $stmtQ->execute([$exerciseId]);
    $questionsRaw = $stmtQ->fetchAll(PDO::FETCH_ASSOC);

    $questions = [];

    /* =========================================
         ORDERING — SLÅ IHOP TILL EN (1) FRÅGA
    ========================================== */
    if ($exercise["Type"] === "ordering") {

        $options = array_map(function ($q) {
            return [
                "Option_Id" => (int)$q["Question_Id"],
                "text"      => $q["Statement"],
                "correct"   => (int)$q["Correct"]
            ];
        }, $questionsRaw);

        $questions[] = [
            "Question_Id" => (int)$exerciseId,
            "Statement"   => "Ordna meningarna i rätt ordning",
            "Correct"     => null,
            "options"     => $options
        ];

    } else {

        /* =========================================
             TRUE/FALSE & MCQ — HA KVAR INDIVIDUELLA
        ========================================== */
        foreach ($questionsRaw as $q) {

            $qData = [
                "Question_Id" => (int)$q["Question_Id"],
                "Statement"   => $q["Statement"],
                "Correct"     => (int)$q["Correct"],
                "options"     => []
            ];

            // MCQ → hämta options
            if ($exercise["Type"] === "mcq") {
                $stmtO = $pdo->prepare("
                    SELECT Option_Id, Option_Text, Is_Correct
                    FROM question_options
                    WHERE Question_Id=?
                ");
                $stmtO->execute([$q["Question_Id"]]);
                $qData["options"] = $stmtO->fetchAll(PDO::FETCH_ASSOC);
            }

            $questions[] = $qData;
        }
    }

    /* =========================================
         HÄMTA KLASSER ÖVNINGEN ÄR TILLDELAD TILL
    ========================================== */

    $stmtC = $pdo->prepare("
        SELECT class_id 
        FROM class_exercises 
        WHERE exercise_id = ?
    ");
    $stmtC->execute([$exerciseId]);
    $assignedClasses = $stmtC->fetchAll(PDO::FETCH_COLUMN);

    /* =========================================
                   SVAR
    ========================================== */
    echo json_encode([
        "success" => true,
        "exercise" => [
            "Exercise_Id"      => (int)$exercise["Exercise_Id"],
            "Title"            => $exercise["Title"],
            "Description"      => $exercise["Description"],
            "Type"             => $exercise["Type"],
            "Max_XP"           => (int)$exercise["Max_XP"],
            "questions"        => $questions,
            "assigned_classes" => $assignedClasses
        ]
    ]);

} catch (Exception $e) {

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);

}
