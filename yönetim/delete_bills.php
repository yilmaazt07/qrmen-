<?php
session_start();

// Giriş kontrolü
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim']);
    exit;
}

// JSON verisini al
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['bill_ids']) || !is_array($input['bill_ids']) || empty($input['bill_ids'])) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz adisyon ID listesi']);
    exit;
}

$bill_ids = $input['bill_ids'];

// Veritabanı bağlantısı
$servername = "localhost";
$username = "ymc15dmasycomtr_qrmenutk";
$password = "1b58b79a!A";
$dbname = "ymc15dmasycomtr_qrmenu";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Transaction başlat
    $conn->beginTransaction();
    
    $deleted_count = 0;
    
    foreach ($bill_ids as $bill_id) {
        // Önce adisyon var mı kontrol et
        $check_stmt = $conn->prepare("SELECT id, toplam_tutar, tarih FROM satislar WHERE id = ?");
        $check_stmt->execute([$bill_id]);
        $bill = $check_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($bill) {
            // Satis detaylarını sil (Foreign key constraint nedeniyle önce detaylar silinmeli)
            $delete_details_stmt = $conn->prepare("DELETE FROM satis_detaylari WHERE satis_id = ?");
            $delete_details_stmt->execute([$bill_id]);
            
            // Ana satış kaydını sil
            $delete_stmt = $conn->prepare("DELETE FROM satislar WHERE id = ?");
            $delete_stmt->execute([$bill_id]);
            
            $deleted_count++;
            
            // Günlük özeti güncelle
            $update_ozet_stmt = $conn->prepare("
                UPDATE gunluk_ozet 
                SET 
                    toplam_satis = toplam_satis - ?,
                    toplam_adisyon = toplam_adisyon - 1,
                    updated_at = CURRENT_TIMESTAMP
                WHERE tarih = ?
            ");
            $update_ozet_stmt->execute([$bill['toplam_tutar'], $bill['tarih']]);
            
            // Eğer günlük özet yoksa oluştur (negatif değerlerle)
            $insert_ozet_stmt = $conn->prepare("
                INSERT INTO gunluk_ozet (tarih, toplam_satis, toplam_adisyon, toplam_urun) 
                VALUES (?, -?, -1, 0)
                ON DUPLICATE KEY UPDATE 
                    toplam_satis = toplam_satis - VALUES(toplam_satis),
                    toplam_adisyon = toplam_adisyon - 1,
                    updated_at = CURRENT_TIMESTAMP
            ");
            $insert_ozet_stmt->execute([$bill['tarih'], $bill['toplam_tutar']]);
        }
    }
    
    // Transaction'ı commit et
    $conn->commit();
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true, 
        'deleted_count' => $deleted_count,
        'message' => "$deleted_count adisyon başarıyla silindi"
    ]);
    
} catch(PDOException $e) {
    // Hata durumunda rollback yap
    $conn->rollback();
    
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Veritabanı hatası: ' . $e->getMessage()
    ]);
}
?>