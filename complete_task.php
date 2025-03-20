<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php?error=No task specified');
    exit;
}

require_once 'db.php';

try {
    $taskId = $_GET['id'];

    $stmt = $conn->prepare("UPDATE tasks SET is_completed = 1 WHERE id = :id");
    $stmt->bindParam(':id', $taskId);
    $stmt->execute();

    header('Location: index.php?success=Task completed');
    exit;
} catch (PDOException $e) {

    header('Location: index.php?error=' . urlencode("Database error: " . $e->getMessage()));
    exit;
}
?>