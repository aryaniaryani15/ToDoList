<?php
include 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: index.php");
    exit();
}

// Ambil data tugas
$result = $conn->query("SELECT * FROM tasks WHERE id=$id AND user_id=$user_id");
$task = $result->fetch_assoc();

if (!$task) {
    header("Location: index.php");
    exit();
}

// Proses update tugas
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $task_text = $_POST['task'];
    

    $conn->query("UPDATE tasks SET task='$task_text', deadline='$deadline' WHERE id=$id AND user_id=$user_id");
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <title>Edit Subtasks</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: #F7CFD8;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .edit-container {
            background: #FFEDFA;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 300px;
            text-align: center;
        }

        input,
        select {
            width: 95%;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }

        button {
            background:  #E195AB;
            color: white;
            padding: 10px;
            border-radius: 8px;
            cursor: pointer;
            border: none;
            width: 100%;
            margin-top: 10px;
        }

        .cancel-btn {
            background: #F7B5CA;
        }
    </style>
</head>

<body>

    <div class="edit-container">
        <h2>Edit Subtasks</h2>
        <form method="POST">
            <input type="text" name="task" value="<?= htmlspecialchars($task['task']) ?>" required>
            <button type="submit">Simpan</button>
            <a href="index.php"><button type="button" class="cancel-btn">Batal</button></a>
        </form>
    </div>
</body>
</html>