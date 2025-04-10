<?php
session_start();
session_destroy(); // Hapus semua sesi
header("Location: login.php"); // Kembali ke halaman login
exit();
?>
