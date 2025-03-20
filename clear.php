<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once 'db.php';

try {

    $stmt = $conn->prepare("DELETE FROM tasks WHERE is_completed = 1 AND user_id = :user_id");
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();

    $rowsAffected = $stmt->rowCount();
    
    if ($rowsAffected > 0) {
        header('Location: index.php?success=' . urlencode("Successfully cleared $rowsAffected completed tasks"));
    } else {
        header('Location: index.php?info=No completed tasks to clear');
    }
    exit;
} catch (PDOException $e) {

    header('Location: index.php?error=' . urlencode("Database error: " . $e->getMessage()));
    exit;
}
?>
