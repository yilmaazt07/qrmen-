<?php
session_start();

// Giriş kontrolü
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Yetkisiz erişim']);
    exit;
}

// Veritabanı bağlantısı
$servername = "localhost";
$username = "ymc15dmasycomtr_qrmenutk";
$password = "1b58b79a!A";
$dbname = "ymc15dmasycomtr_qrmenu";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Aktif adisyonları getir (bugünkü satışlar)
    $stmt = $conn->prepare("
        SELECT 
            id,
            adisyon_no,
            toplam_tutar,
            urun_sayisi,
            TIME_FORMAT(saat, '%H:%i') as saat,
            tarih
        FROM satislar 
        WHERE tarih = CURDATE()
        ORDER BY created_at DESC
    ");
    
    $stmt->execute();
    $bills = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode($bills);
    
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Veritabanı hatası: ' . $e->getMessage()]);
}
?>