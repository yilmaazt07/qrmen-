<?php
session_start();

// Session'ı temizle
session_destroy();

// Cookie'yi sil
if (isset($_COOKIE['admin_kullanici'])) {
    setcookie('admin_kullanici', '', time() - 3600, '/');
}

// Giriş sayfasına yönlendir
header('Location: giris.php');
exit;
?>