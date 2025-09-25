<?php
session_start();

// Veritabanı bağlantısı
include '../baglan.php';

// JSON verisini al
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Hata kontrolü
if (!$data) {
    echo json_encode(['success' => false, 'error' => 'Geçersiz veri formatı']);
    exit;
}

try {
    // Transaction başlat
    $conn->begin_transaction();
    
    // Ana satış kaydını ekle
    $stmt = $conn->prepare("INSERT INTO satislar (tarih, saat, toplam_tutar, urun_sayisi, adisyon_no, kullanici_id) VALUES (?, ?, ?, ?, ?, ?)");
    $kullanici_id = isset($_SESSION['kullanici_id']) ? $_SESSION['kullanici_id'] : 1;
    
    $stmt->bind_param('sssdsi', 
        $data['tarih'], 
        $data['saat'], 
        $data['toplam_tutar'], 
        $data['urun_sayisi'], 
        $data['adisyon_no'], 
        $kullanici_id
    );
    
    if (!$stmt->execute()) {
        throw new Exception('Ana satış kaydı eklenemedi: ' . $stmt->error);
    }
    
    $sale_id = $conn->insert_id;
    
    // Satış detaylarını ekle
    $detail_stmt = $conn->prepare("INSERT INTO satis_detaylari (satis_id, urun_id, urun_adi, miktar, birim_fiyat, toplam_fiyat) VALUES (?, ?, ?, ?, ?, ?)");
    
    foreach ($data['items'] as $item) {
        $urun_id = isset($item['id']) ? $item['id'] : 0;
        $urun_adi = $item['name'];
        $miktar = $item['quantity'];
        $birim_fiyat = $item['price'];
        $toplam_fiyat = $item['total'];
        
        $detail_stmt->bind_param('iisidd', 
            $sale_id, 
            $urun_id, 
            $urun_adi, 
            $miktar, 
            $birim_fiyat, 
            $toplam_fiyat
        );
        
        if (!$detail_stmt->execute()) {
            throw new Exception('Satış detayı eklenemedi: ' . $detail_stmt->error);
        }
    }
    
    // Transaction'ı onayla
    $conn->commit();
    
    // Başarılı yanıt
    echo json_encode([
        'success' => true, 
        'sale_id' => $sale_id,
        'message' => 'Satış verileri başarıyla kaydedildi'
    ]);
    
} catch (Exception $e) {
    // Hata durumunda rollback
    $conn->rollback();
    
    echo json_encode([
        'success' => false, 
        'error' => $e->getMessage()
    ]);
}

// Bağlantıları kapat
if (isset($stmt)) $stmt->close();
if (isset($detail_stmt)) $detail_stmt->close();
$conn->close();
?>