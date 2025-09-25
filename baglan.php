<?php
$servername = "localhost";
$username = "x";
$password = "1!A";
$dbname = "x";

// Veritabanı bağlantısı oluştur
$conn = new mysqli($servername, $username, $password, $dbname);

// Bağlantıyı kontrol et
if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

// Türkçe karakter desteği için
$conn->set_charset("utf8");
?>
