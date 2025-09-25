<?php
// Test verisi ekleme scripti
$servername = "localhost";
$username = "ymc15dmasycomtr_qrmenutk";
$password = "1b58b79a!A";
$dbname = "ymc15dmasycomtr_qrmenu";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Örnek satış verileri ekle
    $test_sales = [
        [
            'tarih' => date('Y-m-d'),
            'saat' => '14:30:00',
            'toplam_tutar' => 125.50,
            'urun_sayisi' => 5,
            'adisyon_no' => 'ADI-001'
        ],
        [
            'tarih' => date('Y-m-d'),
            'saat' => '15:15:00',
            'toplam_tutar' => 89.75,
            'urun_sayisi' => 3,
            'adisyon_no' => 'ADI-002'
        ],
        [
            'tarih' => date('Y-m-d'),
            'saat' => '15:45:00',
            'toplam_tutar' => 67.25,
            'urun_sayisi' => 4,
            'adisyon_no' => 'ADI-003'
        ],
        [
            'tarih' => date('Y-m-d'),
            'saat' => '16:20:00',
            'toplam_tutar' => 234.00,
            'urun_sayisi' => 8,
            'adisyon_no' => 'ADI-004'
        ]
    ];
    
    $stmt = $conn->prepare("
        INSERT INTO satislar (tarih, saat, toplam_tutar, urun_sayisi, adisyon_no, kullanici_id) 
        VALUES (?, ?, ?, ?, ?, 1)
    ");
    
    foreach ($test_sales as $sale) {
        $stmt->execute([
            $sale['tarih'],
            $sale['saat'],
            $sale['toplam_tutar'],
            $sale['urun_sayisi'],
            $sale['adisyon_no']
        ]);
    }
    
    echo "Test verileri başarıyla eklendi!\n";
    echo count($test_sales) . " adet örnek adisyon oluşturuldu.\n";
    
} catch(PDOException $e) {
    echo "Hata: " . $e->getMessage() . "\n";
}
?>