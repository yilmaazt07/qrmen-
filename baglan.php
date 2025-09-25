<?php
$servername = "localhost";
$username = "ymc15dmasycomtr_qrmenutk";
$password = "1b58b79a!A";
$dbname = "ymc15dmasycomtr_qrmenu";

// Veritabanı bağlantısı oluştur
$conn = new mysqli($servername, $username, $password, $dbname);

// Bağlantıyı kontrol et
if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

// Türkçe karakter desteği için
$conn->set_charset("utf8");
?>