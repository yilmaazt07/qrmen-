<?php
session_start();

// Giriş kontrolü
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim']);
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
    
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'add_acik_hesap':
            addAcikHesap($conn);
            break;
            
        case 'add_veresiye':
            addVeresiye($conn);
            break;
            
        case 'odeme_yap':
            odemeYap($conn);
            break;
            
        case 'get_hesap_list':
            getHesapList($conn);
            break;
            
        case 'add_to_account':
            addToAccount($conn);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Geçersiz işlem']);
    }
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
}

function addAcikHesap($conn) {
    $hesap_adi = $_POST['hesap_adi'] ?? '';
    $firma_adi = $_POST['firma_adi'] ?? '';
    $yetkili_kisi = $_POST['yetkili_kisi'] ?? '';
    $telefon = $_POST['telefon'] ?? '';
    $email = $_POST['email'] ?? '';
    $adres = $_POST['adres'] ?? '';
    $kredi_limiti = floatval($_POST['kredi_limiti'] ?? 0);
    $notlar = $_POST['notlar'] ?? '';
    
    if (empty($hesap_adi)) {
        echo json_encode(['success' => false, 'message' => 'Hesap adı zorunludur']);
        return;
    }
    
    $stmt = $conn->prepare("
        INSERT INTO acik_hesaplar 
        (hesap_adi, firma_adi, yetkili_kisi, telefon, email, adres, kredi_limiti, notlar) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $hesap_adi, $firma_adi, $yetkili_kisi, $telefon, 
        $email, $adres, $kredi_limiti, $notlar
    ]);
    
    echo json_encode(['success' => true, 'message' => 'Açık hesap başarıyla eklendi']);
}

function addVeresiye($conn) {
    $ad_soyad = $_POST['ad_soyad'] ?? '';
    $telefon = $_POST['telefon'] ?? '';
    $adres = $_POST['adres'] ?? '';
    $notlar = $_POST['notlar'] ?? '';
    
    if (empty($ad_soyad)) {
        echo json_encode(['success' => false, 'message' => 'Ad soyad zorunludur']);
        return;
    }
    
    $stmt = $conn->prepare("
        INSERT INTO veresiye_musteriler 
        (ad_soyad, telefon, adres, notlar) 
        VALUES (?, ?, ?, ?)
    ");
    
    $stmt->execute([$ad_soyad, $telefon, $adres, $notlar]);
    
    echo json_encode(['success' => true, 'message' => 'Veresiye müşteri başarıyla eklendi']);
}

function odemeYap($conn) {
    $hesap_tipi = $_POST['hesap_tipi'] ?? '';
    $hesap_id = intval($_POST['hesap_id'] ?? 0);
    $odeme_tutari = floatval($_POST['odeme_tutari'] ?? 0);
    $odeme_yontemi = $_POST['odeme_yontemi'] ?? 'nakit';
    $aciklama = $_POST['aciklama'] ?? '';
    
    if ($odeme_tutari <= 0) {
        echo json_encode(['success' => false, 'message' => 'Geçerli bir ödeme tutarı girin']);
        return;
    }
    
    if (!in_array($hesap_tipi, ['acik_hesap', 'veresiye'])) {
        echo json_encode(['success' => false, 'message' => 'Geçersiz hesap tipi']);
        return;
    }
    
    // Ödeme kaydını ekle
    $stmt = $conn->prepare("
        INSERT INTO hesap_odemeleri 
        (hesap_tipi, hesap_id, odeme_tutari, odeme_tarihi, odeme_saati, odeme_yontemi, aciklama, kullanici_id) 
        VALUES (?, ?, ?, CURDATE(), CURTIME(), ?, ?, ?)
    ");
    
    $stmt->execute([
        $hesap_tipi, $hesap_id, $odeme_tutari, 
        $odeme_yontemi, $aciklama, $_SESSION['admin_id']
    ]);
    
    echo json_encode(['success' => true, 'message' => 'Ödeme başarıyla kaydedildi']);
}

function getHesapList($conn) {
    $hesap_tipi = $_GET['tip'] ?? '';
    
    if ($hesap_tipi === 'acik_hesap') {
        $stmt = $conn->prepare("
            SELECT id, hesap_adi as name, firma_adi, kredi_limiti, mevcut_borc,
                   (kredi_limiti - mevcut_borc) as kullanilabilir_limit
            FROM acik_hesaplar 
            WHERE durum = 'aktif' AND (kredi_limiti - mevcut_borc) > 0
            ORDER BY hesap_adi ASC
        ");
        $stmt->execute();
        $hesaplar = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'hesaplar' => $hesaplar]);
        
    } elseif ($hesap_tipi === 'veresiye') {
        $stmt = $conn->prepare("
            SELECT id, ad_soyad as name, telefon, mevcut_borc
            FROM veresiye_musteriler 
            WHERE durum = 'aktif'
            ORDER BY ad_soyad ASC
        ");
        $stmt->execute();
        $hesaplar = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'hesaplar' => $hesaplar]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Geçersiz hesap tipi']);
    }
}

function addToAccount($conn) {
    $hesap_tipi = $_POST['hesap_tipi'] ?? '';
    $hesap_id = intval($_POST['hesap_id'] ?? 0);
    $tutar = floatval($_POST['tutar'] ?? 0);
    $aciklama = $_POST['aciklama'] ?? '';
    $adisyon_data = json_decode($_POST['adisyon_data'] ?? '[]', true);
    
    if ($tutar <= 0) {
        echo json_encode(['success' => false, 'message' => 'Geçerli bir tutar girin']);
        return;
    }
    
    if (!in_array($hesap_tipi, ['acik_hesap', 'veresiye'])) {
        echo json_encode(['success' => false, 'message' => 'Geçersiz hesap tipi']);
        return;
    }
    
    $conn->beginTransaction();
    
    try {
        // Satış kaydını ekle
        $stmt = $conn->prepare("
            INSERT INTO satislar 
            (tarih, saat, toplam_tutar, urun_sayisi, adisyon_no, kullanici_id, hesap_tipi, hesap_id) 
            VALUES (CURDATE(), CURTIME(), ?, ?, ?, ?, ?, ?)
        ");
        
        $urun_sayisi = array_sum(array_column($adisyon_data, 'quantity'));
        $adisyon_no = 'HSP-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        $stmt->execute([
            $tutar, $urun_sayisi, $adisyon_no, 
            $_SESSION['admin_id'], $hesap_tipi, $hesap_id
        ]);
        
        $satis_id = $conn->lastInsertId();
        
        // Satış detaylarını ekle
        if (!empty($adisyon_data)) {
            $stmt = $conn->prepare("
                INSERT INTO satis_detaylari 
                (satis_id, urun_id, urun_adi, miktar, birim_fiyat, toplam_fiyat) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            foreach ($adisyon_data as $item) {
                $stmt->execute([
                    $satis_id,
                    $item['id'] ?? 0,
                    $item['name'],
                    $item['quantity'],
                    $item['price'],
                    $item['quantity'] * $item['price']
                ]);
            }
        }
        
        // Hesap hareketini ekle
        $stmt = $conn->prepare("
            INSERT INTO hesap_hareketleri 
            (hesap_tipi, hesap_id, hareket_tipi, tutar, aciklama, adisyon_id, tarih, saat, kullanici_id) 
            VALUES (?, ?, 'borc', ?, ?, ?, CURDATE(), CURTIME(), ?)
        ");
        
        $stmt->execute([
            $hesap_tipi, $hesap_id, $tutar, 
            $aciklama ?: 'Adisyon - ' . $adisyon_no, 
            $satis_id, $_SESSION['admin_id']
        ]);
        
        $conn->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Adisyon hesaba başarıyla eklendi',
            'adisyon_no' => $adisyon_no
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'İşlem hatası: ' . $e->getMessage()]);
    }
}
?>