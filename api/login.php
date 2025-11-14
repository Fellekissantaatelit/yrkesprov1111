<?php
session_start();
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$host = 'localhost';
$db = 'frågesport db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? '';


    if ($action === 'login') {
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['username'] = $username;
                echo json_encode(['success' => true, 'message' => 'Inloggning lyckad']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Fel lösenord']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Användare finns inte']);
        }
    }

    
    elseif ($action === 'logout') {
        session_unset();
        session_destroy();
        echo json_encode(['success' => true, 'message' => 'Utloggad']);
    }

    // -------------------- INVALID ACTION --------------------
    else {
        echo json_encode(['success' => false, 'message' => 'Ogiltig action']);
    }

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
