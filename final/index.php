<?php
include 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Tambah tugas utama
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['task'])) {
    $task = $_POST['task'];
    $priority = $_POST['priority'];
    $deadline = $_POST['deadline'];

    $conn->query("INSERT INTO tasks (user_id, task, priority, deadline, status) 
                  VALUES ('$user_id', '$task', '$priority', '$deadline', 'pending')");
    $today = date('Y-m-d');
    if ($deadline < $today) {
        echo "<script>
                alert('Ada tugas yang sudah terlambat! Segera selesaikan');
                window.location.href = 'index.php';
              </script>";
    exit();
}
}
// Tambah subtugas
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['subtask'])) {
    $task_id = $_POST['task_id'];
    $subtask = $_POST['subtask'];

    $conn->query("INSERT INTO tasks (user_id, task, priority, deadline, parent_task_id, status) 
                  VALUES ('$user_id', '$subtask', 'low', '2025-12-31', '$task_id', 'pending')");
    header("Location: index.php");
exit();
}

// Hapus tugas atau subtugas
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM tasks WHERE id=$id AND user_id=$user_id");
    header("Location: index.php");
    exit();
}

// Hapus subtugas
if (isset($_GET['delete_subtask'])) {
    $subtask_id = $_GET['delete_subtask'];
    $conn->query("DELETE FROM tasks WHERE id=$subtask_id AND user_id=$user_id");
    header("Location: index.php");
    exit();
}

// Update status tugas utama & subtugas
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['task_id']) && isset($_POST['completed'])) {
    $task_id = $_POST['task_id'];
    $status = $_POST['completed'] == 'true' ? 'completed' : 'pending';
    
    $conn->query("UPDATE tasks SET status='$status' WHERE id=$task_id AND user_id=$user_id");
    $conn->query("UPDATE tasks SET status='$status' WHERE parent_task_id=$task_id AND user_id=$user_id");
    exit();
}

// Update subtugas
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['subtask_id']) && isset($_POST['subtask_completed'])) {
    $subtask_id = $_POST['subtask_id'];
    $status = $_POST['subtask_completed'] == 'true' ? 'completed' : 'pending';
    
    $conn->query("UPDATE tasks SET status='$status' WHERE id=$subtask_id AND user_id=$user_id");
    exit();
}

// Edit subtask
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_subtask_id'])) {
    $subtask_id = $_POST['edit_subtask_id'];
    $new_subtask = $_POST['new_subtask'];
    
    $conn->query("UPDATE tasks SET task='$new_subtask' WHERE id=$subtask_id AND user_id=$user_id");
    header("Location: index.php");
    exit();
}

$result = $conn->query("SELECT * FROM tasks WHERE user_id=$user_id AND parent_task_id IS NULL 
                        ORDER BY FIELD(priority, 'high', 'medium', 'low'), deadline ASC");

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <title>To-Do List</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: #F7CFD8;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            width: 80%;
            max-width: 800px;
        }

        h2 {
            text-align: center;
            color: #fff;
        }

        .logout-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            background: #E195AB;
            color: white;
            padding: 10px 15px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 18px;
        }

        .todo-input {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        input,
        select {
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ddd;
            font-size: 16px;
        }

        button {
            background: #E195AB;
            color: white;
            padding: 10px;
            border-radius: 8px;
            cursor: pointer;
            border: none;
        }

        .task-list {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }

        .task-card {
            background: #fff;
            padding: 10px;
            /* Mengurangi padding untuk memperkecil ukuran */
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 230px;
            /* Memperkecil lebar card */
            height: auto;
            /* Mengatur height otomatis agar semua konten muat */
            position: relative;
            font-size: 13px;
            /* Menyesuaikan ukuran font untuk lebih pas */
            display: flex;
            flex-direction: column;
        }



        .task-header {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .task-card.completed h3,
        .task-card.completed .subtasks li {
            text-decoration: line-through;
            color: gray;
        }

        .priority {
            font-size: 12px;
            color: white;
            padding: 5px 8px;
            /* Tambah padding */
            border-radius: 5px;
            display: inline-block;
            max-width: 50px;
            /* Ubah dari 25px ke 50px */
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            text-align: center;
            /* Biar lebih rapi */
        }


        .low {
            background: #81c784;
        }

        .medium {
            background: #ffb74d;
        }

        .high {
            background: #e57373;
        }

        .delete-btn,
        .edit-btn {
            position: absolute;
            top: 10px;
            cursor: pointer;
        }

        .delete-btn {
            color: red;
            right: 10px;
        }

        .edit-btn {
            color: blue;
            right: 35px;
        }

        .subtasks {
            margin-top: 10px;
            padding-left: 15px;
            font-size: 14px;
        }

        .warning {
            color: red;
            font-weight: bold;
        }

        .subtask-checkbox {
            margin-right: 5px;
        }

        .task-card form {
            display: flex;
            gap: 8px;
            margin-top: 10px;
        }

        .task-card input[type="text"] {
            width: 75%;
            /* Menyesuaikan lebar input */
            padding: 5px;
            /* Mengurangi padding */
            font-size: 12px;
            /* Menyesuaikan ukuran font */
        }

        .task-card button {
            padding: 10px 9px;
            /* Mengurangi ukuran tombol */
            font-size: 13px;
            /* Menyesuaikan ukuran font tombol */
            cursor: pointer;
        }

        .subtasks li {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-right: 10px;
        }

        .subtasks .edit-btn2,
        .subtasks .delete-btn2 {
        margin-left: 3px; /* Biar ada jarak antara teks dan ikon */
        color: gray;
        }

        .subtasks .edit-btn2:hover,
        .subtasks .delete-btn2:hover {
        color: black; /* Warna lebih jelas saat hover */
        }


        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            width: 300px;
        }

        .modal-buttons {
            margin-top: 15px;
            display: flex;
            justify-content: space-around;
        }

        .modal-buttons button {
            padding: 10px 15px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }

        #confirmLogout {
            background: red;
            color: white;
        }

        #cancelLogout {
            background: gray;
            color: white;
        }
        </style>

    <script>
        function updateTaskStatus(taskId, checkbox) {
            const completed = checkbox.checked ? 'true' : 'false';

            fetch('index.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `task_id=${taskId}&completed=${completed}`
                })
                .then(() => {
                    const taskCard = document.getElementById(`task-${taskId}`);
                    const subtasks = taskCard.querySelectorAll('.subtasks li');

                    if (completed === 'true') {
                        taskCard.classList.add('completed');
                        subtasks.forEach(subtask => {
                            const subCheckbox = subtask.querySelector('input[type="checkbox"]');
                            subCheckbox.checked = true;
                            subtask.style.textDecoration = 'line-through';
                        });
                    } else {
                        taskCard.classList.remove('completed');
                        subtasks.forEach(subtask => {
                            const subCheckbox = subtask.querySelector('input[type="checkbox"]');
                            subCheckbox.checked = false;
                            subtask.style.textDecoration = 'none';
                        });
                    }
                });
        }

        function updateSubtaskStatus(subtaskId, checkbox) {
            const completed = checkbox.checked ? 'true' : 'false';

            fetch('index.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `subtask_id=${subtaskId}&subtask_completed=${completed}`
                })
                .then(() => {
                    const subtaskElement = document.getElementById(`subtask-${subtaskId}`);
                    if (completed === 'true') {
                        subtaskElement.style.textDecoration = 'line-through';
                    } else {
                        subtaskElement.style.textDecoration = 'none';
                    }

                    // If all subtasks are checked, check the main task
                    const taskId = subtaskElement.closest('.task-card').id.replace('task-', '');
                    const subtasks = document.querySelectorAll(`#task-${taskId} .subtasks input[type="checkbox"]`);
                    const allChecked = Array.from(subtasks).every(sub => sub.checked);

                    const mainCheckbox = document.querySelector(`#task-${taskId} input[type="checkbox"]`);
                    mainCheckbox.checked = allChecked;
                });
        }
    </script>
</head>

<body>

    <a href="logout.php" class="logout-btn">Logout</a>
    <div id="logoutModal" class="modal">
        <div class="modal-content">
            <p>Anda yakin ingin logout?</p>
            <div class="modal-buttons">
                <button id="confirmLogout">Ya</button>
                <button id="cancelLogout">Batal</button>
            </div>
        </div>
    </div>


    <div class="container">
        <h2>To-Do List</h2>

        <form method="POST" class="todo-input">
            <input type="text" name="task" placeholder="Tambah tugas baru..." required>
            <select name="priority">
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
            </select>
            <input type="date" name="deadline" required>
            <button type="submit">Tambah</button>
        </form>

        <div class="task-list"> 
            <?php while ($row = $result->fetch_assoc()): ?>
                <?php
                $deadline_date = strtotime($row['deadline']);
                $today = strtotime(date('Y-m-d'));
                $overdue = $today > $deadline_date;
                ?>
                <div class="task-card <?= $row['status'] === 'completed' ? 'completed' : '' ?>" id="task-<?= $row['id'] ?>">
                    <div class="task-header">
                        <input type="checkbox" <?= $row['status'] === 'completed' ? 'checked' : '' ?> onclick="updateTaskStatus(<?= $row['id'] ?>, this)">
                        <h3><?= htmlspecialchars($row['task']) ?></h3>
                    </div>
                    <span class="priority <?= $row['priority'] ?>"><?= ucfirst($row['priority']) ?></span>
                    <p>Deadline: <?= $row['deadline'] ?></p>
                    <?php if ($overdue && $row['status'] !== 'completed'): ?>
                        <p class="warning">Tugas sudah melewati batas waktu!</p>
                    <?php endif; ?>
                    <a href="edit.php?id=<?= $row['id'] ?>" class="edit-btn"><i class="fas fa-edit"></i></a>
                    <a href="?delete=<?= $row['id'] ?>" class="delete-btn" onclick="return confirmDelete(event, <?= $row['id'] ?>)"><i class="fas fa-times"></i></a>


                    <div class="subtasks">
                        <strong>Subtasks:</strong>
                        <ul>
                            <?php
                            $subtasks = $conn->query("SELECT * FROM tasks WHERE parent_task_id={$row['id']}");
                            while ($sub = $subtasks->fetch_assoc()):
                            ?>
                                <li id="subtask-<?= $sub['id'] ?>">
                                    <input type="checkbox" class="subtask-checkbox" <?= $sub['status'] === 'completed' ? 'checked' : '' ?> onclick="updateSubtaskStatus(<?= $sub['id'] ?>, this)">
                                    <span><?= htmlspecialchars($sub['task']) ?> </span>
                                    <span>
                                    <a href="edit_subtask.php?id=<?= $sub['id'] ?>" class="edit-btn2"><i class="fas fa-edit"></i></a>
                                    <a href="?delete_subtask=<?= $sub['id'] ?>" class="delete-btn2" onclick="return confirmDelete(event, <?= $row['id'] ?>)"><i class="fas fa-times"></i></a>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                        <form method="POST" class="subtask-from">
                            <input type="hidden" name="task_id" value="<?= $row['id'] ?>">
                            <input type="text" name="subtask" placeholder="Tambah subtugas..." required>
                            <button type="submit">+</button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const logoutBtn = document.querySelector(".logout-btn");
            const modal = document.getElementById("logoutModal");
            const confirmBtn = document.getElementById("confirmLogout");
            const cancelBtn = document.getElementById("cancelLogout");

            logoutBtn.addEventListener("click", function(event) {
                event.preventDefault(); // Cegah redirect langsung
                modal.style.display = "flex"; // Tampilkan modal
            });

            confirmBtn.addEventListener("click", function() {
                window.location.href = logoutBtn.href; // Redirect ke logout.php
            });

            cancelBtn.addEventListener("click", function() {
                modal.style.display = "none"; // Sembunyikan modal
            });

            // Tutup modal jika klik di luar konten modal
            window.addEventListener("click", function(event) {
                if (event.target === modal) {
                    modal.style.display = "none";
                }
            });
        });

    document.addEventListener("DOMContentLoaded", function () {
        const deleteButtons = document.querySelectorAll('.delete-btn');

        deleteButtons.forEach(btn => {
            btn.addEventListener('click', function (event) {
                const isConfirmed = confirm("Apakah anda yakin untuk menghapus tugas ini?");
                if (!isConfirmed) {
                    event.preventDefault(); // Batalkan aksi hapus
                }
            });
        });
    });

    document.addEventListener("DOMContentLoaded", function () {
        const deleteButtons = document.querySelectorAll('.delete-btn2');

        deleteButtons.forEach(btn => {
            btn.addEventListener('click', function (event) {
                const isConfirmed = confirm("Apakah anda yakin untuk menghapus subtugas ini?");
                if (!isConfirmed) {
                    event.preventDefault(); // Batalkan aksi hapus
                }
            });
        });
    });


    </script>
</body>
</html>