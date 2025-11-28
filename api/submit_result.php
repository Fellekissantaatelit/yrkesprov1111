<?php
require_once "config.php";
require_once "Session.php";

if (!isset($_SESSION['user']['id'])) {
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit;
}

$userId = $_SESSION['user']['id'];

$input = json_decode(file_get_contents('php://input'), true);
$exerciseId = $input['exercise_id'] ?? null;
$answers = $input['answers'] ?? [];

if (!$exerciseId) {
    echo json_encode(["success" => false, "message" => "Exercise ID saknas"]);
    exit;
}

try {
    $pdo->beginTransaction();

    // --- Hämta övning ---
    $stmtEx = $pdo->prepare("SELECT * FROM exercises WHERE Exercise_Id=?");
    $stmtEx->execute([$exerciseId]);
    $exercise = $stmtEx->fetch(PDO::FETCH_ASSOC);
    if (!$exercise) throw new Exception("Övning hittades inte");

    $type = $exercise['Type'];

    // --- Hämta frågor ---
    $stmtQ = $pdo->prepare("SELECT * FROM exercise_questions WHERE Exercise_Id=? ORDER BY Question_Id ASC");
    $stmtQ->execute([$exerciseId]);
    $questions = $stmtQ->fetchAll(PDO::FETCH_ASSOC);

    $correctCount = 0;
    $totalQuestions = count($questions);

    foreach ($questions as $q) {
        $qId = $q['Question_Id'];
        $userAnswer = $answers[$qId] ?? null;

        $isCorrect = false;

        // ========================================
        // TRUE/FALSE
        // ========================================
        if ($type === 'true_false') {
            $isCorrect = intval($userAnswer) === intval($q['Correct']);
        }

        // ========================================
        // MULTIPLE CHOICE
        // user sends Option_Id
        // ========================================
        elseif ($type === 'mcq') {
            $stmtO = $pdo->prepare("SELECT Option_Id, Is_Correct FROM question_options WHERE Question_Id=?");
            $stmtO->execute([$qId]);
            $opts = $stmtO->fetchAll(PDO::FETCH_ASSOC);

            foreach ($opts as $opt) {
                if ($opt['Is_Correct'] == 1 && $opt['Option_Id'] == $userAnswer) {
                    $isCorrect = true;
                    break;
                }
            }
        }

        // ========================================
        // ORDERING
        // user sends [Option_Id, Option_Id, ...]
        // ========================================
        elseif ($type === 'ordering') {

            // Hämta rätt ordning baserat på Question_Id (ASC)
            $correctOrder = array_map(fn($row) => $row['Question_Id'], $questions);

            // Jämför båda arrayerna
            if ($userAnswer === $correctOrder) {
                $isCorrect = true;
            }
        }

        // ========================================
        // MATCH / FILL-BLANK (fixar vi senare)
        // ========================================

        if ($isCorrect) $correctCount++;
    }

    $percentCorrect = ($totalQuestions > 0) ? ($correctCount / $totalQuestions) * 100 : 0;

    $completed = $percentCorrect >= 70 ? 1 : 0;
    $score = $completed ? intval($exercise['Max_XP']) : 0;

    // --- Spara resultat ---
    $stmtRes = $pdo->prepare("INSERT INTO user_results (User_Id, Exercise_Id, Score, Completed)
                              VALUES (?, ?, ?, ?)
                              ON DUPLICATE KEY UPDATE Score=?, Completed=?, Completed_At=CURRENT_TIMESTAMP()");
    $stmtRes->execute([$userId, $exerciseId, $score, $completed, $score, $completed]);

    // --- Uppdatera XP ---
    if ($completed) {
        $stmtXP = $pdo->prepare("UPDATE users SET xp = xp + ? WHERE u_id=?");
        $stmtXP->execute([$score, $userId]);
    }

    $pdo->commit();

    echo json_encode([
        "success" => true,
        "completed" => $completed,
        "percent_correct" => round($percentCorrect, 2),
        "xp_earned" => $score
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
