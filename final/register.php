<?php
include 'koneksi.php'; // Hubungkan ke database

$success = false; // Variabel untuk menampilkan pop-up

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_lengkap = $_POST['nama_lengkap'];
    $username = $_POST['username'];
    $password_raw = $_POST['password'];
if (strlen($password_raw) < 8) {
    $error = "Password harus minimal 8 karakter!";
} else {
    $password = password_hash($password_raw, PASSWORD_DEFAULT);
    // Cek apakah username sudah ada
    $check = $conn->query("SELECT * FROM users WHERE username='$username'");
    if ($check->num_rows > 0) {
        $error = "Username sudah digunakan, silakan pilih username lain!";
    } else {
        $sql = "INSERT INTO users (nama_lengkap, username, password) VALUES ('$nama_lengkap', '$username', '$password')";
        if ($conn->query($sql) === TRUE) {
            $success = true;
        } else {
            $error = "Registrasi gagal: " . $conn->error;
        }
    }
}

    // Cek apakah username sudah ada
    $check = $conn->query("SELECT * FROM users WHERE username='$username'");
    if ($check->num_rows > 0) {
        $error = "Username sudah digunakan, silakan pilih username lain!";
    } else {
        $sql = "INSERT INTO users (nama_lengkap, username, password) VALUES ('$nama_lengkap', '$username', '$password')";
        if ($conn->query($sql) === TRUE) {
            $success = true; // Set success ke true agar pop-up muncul
        } else {
            $error = "Registrasi gagal: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Register</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: #F7CFD8;
            font-family: Arial, sans-serif;
        }

        .register-container {
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

    <div class="register-container">
        <h2>Registrasi</h2>
        <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
        <form method="POST">
            <input type="text" name="nama_lengkap" placeholder="Nama Lengkap" required><br>
            <input type="text" name="username" placeholder="Username" required><br>
            <input type="password" name="password" placeholder="Password" required minlength="8"><br>
            <button type="submit">Register</button>
        </form>
        <p>Sudah punya akun? <a href="login.php">Login</a></p>
    </div>

    <!-- Pop-up sukses -->
    <div class="popup" id="successPopup">
        <p>Anda berhasil registrasi, silakan login</p>
        <button onclick="redirectToLogin()">OK</button>
    </div>

    <script>
        // Jika registrasi berhasil, tampilkan pop-up
        <?php if ($success): ?>
            document.getElementById("successPopup").style.display = "block";
        <?php endif; ?>

        // Fungsi untuk mengarahkan ke login
        function redirectToLogin() {
            window.location.href = "login.php";
        }
    </script>
</body>
</html>