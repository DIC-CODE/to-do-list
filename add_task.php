<?php
session_start();

// checl user log in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// check form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate the input
    if (empty($_POST['task_name'])) {
        // Redirect back with error
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
        
        
        $sql = "INSERT INTO tasks (task_name, is_completed, created_at) VALUES (:task_name, 0, :created_at)";
        $params = [
            ':task_name' => $taskName,
            ':created_at' => $currentTime
        ];
        
        // Execute 
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        
        // Redirect back to index page
        header('Location: index.php?success=Task added successfully');
        exit;
    } catch (PDOException $e) {
        // Redirect with error message
        header('Location: index.php?error=' . urlencode("Database error: " . $e->getMessage()));
        exit;
    }
} else {
    // not a POST request, redirect to index
    header('Location: index.php');
    exit;
}
?>