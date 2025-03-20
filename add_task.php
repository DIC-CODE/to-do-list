<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (empty($_POST['task_name'])) {

        header('Location: index.php?error=Task name cannot be empty');
        exit;
    }
    
    require_once 'db.php';
    
    try {
        
        $stmt = $conn->prepare("SHOW COLUMNS FROM tasks");
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        
        $taskName = trim($_POST['task_name']);
        $currentTime = date('Y-m-d H:i:s');
        
        
        $sql = "INSERT INTO tasks (task_name, is_completed, created_at, user_id) VALUES (:task_name, 0, :created_at, :user_id)";
        $params = [
            ':task_name' => $taskName,
            ':created_at' => $currentTime,
            ':user_id' => $_SESSION['user_id']
        ];
        

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        

        header('Location: index.php?success=Task added successfully');
        exit;
    } catch (PDOException $e) {

        header('Location: index.php?error=' . urlencode("Database error: " . $e->getMessage()));
        exit;
    }
} else {

    header('Location: index.php');
    exit;
}
?>
