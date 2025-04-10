<?php
include 'koneksi.php'; // Hubungkan ke database

$success = false; // Variabel untuk menampilkan pop-up sukses

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Pakai prepared statement untuk keamanan
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $success = true;
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }

    $stmt->close();
}
?>


<!DOCTYPE html>
<html lang="id">

<head>
    <title>Login</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: #F7CFD8;
            font-family: Arial, sans-serif;
        }

        .login-container {
            background: #FFEDFA;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 300px;
        }

        input {
            width: 90%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            width: 100%;
            padding: 10px;
            background: #E195AB;
            border: none;
            color: white;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
        }

        button:hover {
            background: #F7CFD8;
        }

        p {
            font-size: 14px;
        }

        /* Styling pop-up */
        .popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
            text-align: center;
            border-radius: 10px;
            z-index: 1000;
        }

        .popup button {
            margin-top: 10px;
            padding: 8px 15px;
            cursor: pointer;
            background: #E195AB;
            color: white;
            border: none;
            border-radius: 5px;
        }
    </style>
</head>

<body>

    <div class="login-container">
        <h2>Login</h2>
        <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required><br>
            <input type="password" name="password" placeholder="Password" required minlength="8"><br>
            <button type="submit">Login</button>
        </form>
        <p>Belum punya akun? <a href="register.php">Register</a></p>
    </div>

    <!-- Pop-up sukses -->
    <div class="popup" id="successPopup">
        <p>Berhasil login</p>
        <button onclick="redirectToIndex()">OK</button>
    </div>

    <script>
        // Jika login berhasil, tampilkan pop-up
        <?php if ($success): ?>
            document.getElementById("successPopup").style.display = "block";
        <?php endif; ?>

        // Fungsi untuk mengarahkan ke halaman To-Do List
        function redirectToIndex() {
            window.location.href = "index.php";
        }
    </script>

</body>

</html>