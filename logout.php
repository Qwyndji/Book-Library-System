<?php
session_start();
session_unset();  // Hapus semua variabel session
session_destroy(); // Hancurkan sesi
header("Location: ../auth/login.php"); // Redirect ke halaman login
exit;
?>