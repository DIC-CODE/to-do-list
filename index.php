<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once 'db.php';

$incompleteTasks = [];
$completedTasks = [];
$totalCompletedTasks = 0;
$tasksPerPage = 5;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($currentPage < 1) $currentPage = 1;
$offset = ($currentPage - 1) * $tasksPerPage;

try {
    $stmt = $conn->prepare("SHOW COLUMNS FROM tasks");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $stmt = $conn->prepare("SELECT * FROM tasks WHERE is_completed = 0 AND user_id = :user_id ORDER BY created_at DESC");
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $incompleteTasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $conn->prepare("SELECT COUNT(*) FROM tasks WHERE is_completed = 1 AND user_id = :user_id");
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $totalCompletedTasks = $stmt->fetchColumn();

    $stmt = $conn->prepare("SELECT * FROM tasks WHERE is_completed = 1 AND user_id = :user_id ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->bindParam(':limit', $tasksPerPage, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $completedTasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $totalPages = ceil($totalCompletedTasks / $tasksPerPage);
    if ($currentPage > $totalPages && $totalPages > 0) {
        header('Location: index.php?page=' . $totalPages);
        exit;
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Todo List</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="main-container">
        <div class="sidebar">
            <div class="sidebar-header">TODO LIST</div>
            <div class="sidebar-item">New Task</div>
            <div class="sidebar-item"><a href="clear.php" class="clear-task" onclick="return confirm('Are you sure you want to clear all completed tasks? This action cannot be undone.');">Clear Completed Task</a></div>
            <div class="sidebar-item"><a href="logout.php" class="logout-link" onclick="return confirm('Are you sure you want to Logout?');">Logout</a></div>
        </div>  

        <div class="content">
            <div class="welcome-section">
                <p>Yo wassup  <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'User'; ?>?!</p>
            </div>

            <div class="section">
                <h3>New Task</h3>
                <form action="add_task.php" method="POST" class="add-task-form">
                    <input type="text" name="task_name" placeholder="Task Name" required>
                    <button type="submit" class="add-task-btn">Add Task</button>
                </form>
            </div>

            <div class="section">
                <h3>Task Lists</h3>
                <div class="task-list">
                    <?php if (!empty($incompleteTasks)): ?>
                        <?php foreach ($incompleteTasks as $task): ?>
                            <div class="task-item">
                                <span class="task-name"><?php echo htmlspecialchars($task['task_name']); ?></span>
                                <div class="task-actions">
                                    <a href="complete_task.php?id=<?php echo $task['id']; ?>" class="btn-complete">Complete</a>
                                    <a href="delete_task.php?id=<?php echo $task['id']; ?>" class="btn-delete">Delete</a>
                                    <a href="edit_task.php?id=<?php echo $task['id']; ?>" class="btn-edit">Edit</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-tasks">No active tasks</div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="section">
                <h3>Completed Tasks</h3>
                <div class="completed-tasks-table">
                    <?php if (!empty($completedTasks)): ?>
                        <table class="tasks-table">
                            <thead>
                                <tr>
                                    <th>Task Name</th>
                                    <th>Completion Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($completedTasks as $task): ?>
                                    <tr class="completed-task-row">
                                        <td><?php echo htmlspecialchars($task['task_name']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($task['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                        <div class="pagination">
                            <?php if ($totalPages > 1): ?>
                                <?php if ($currentPage > 1): ?>
                                    <a href="?page=<?php echo $currentPage - 1; ?>" class="pagination-btn">&laquo; Previous</a>
                                <?php endif; ?>
                                
                                <span class="pagination-info">Page <?php echo $currentPage; ?> of <?php echo $totalPages; ?></span>
                                
                                <?php if ($currentPage < $totalPages): ?>
                                    <a href="?page=<?php echo $currentPage + 1; ?>" class="pagination-btn">Next &raquo;</a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-tasks">No completed tasks</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
