<?php
session_start();

// Giriş kontrolü
if (!isset($_SESSION['admin_id'])) {
    header('Location: giris.php');
    exit;
}

// Veritabanı bağlantı bilgileri
$servername = "localhost";
$username = "ymc15dmasycomtr_qrmenutk";
$password = "1b58b79a!A";
$dbname = "ymc15dmasycomtr_qrmenu";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Filtre parametreleri
    $filter = isset($_GET['filter']) ? $_GET['filter'] : 'gunluk';
    $tarih = isset($_GET['tarih']) ? $_GET['tarih'] : date('Y-m-d');
    
    // Tarih aralığını belirle
    switch($filter) {
        case 'gunluk':
            $start_date = $tarih;
            $end_date = $tarih;
            break;
        case 'haftalik':
            $start_date = date('Y-m-d', strtotime('monday this week', strtotime($tarih)));
            $end_date = date('Y-m-d', strtotime('sunday this week', strtotime($tarih)));
            break;
        case 'aylik':
            $start_date = date('Y-m-01', strtotime($tarih));
            $end_date = date('Y-m-t', strtotime($tarih));
            break;
        default:
            $start_date = $tarih;
            $end_date = $tarih;
    }
    
    // Genel istatistikler
    $stmt = $conn->prepare("SELECT 
        COUNT(*) as toplam_adisyon,
        SUM(toplam_tutar) as toplam_ciro,
        AVG(toplam_tutar) as ortalama_adisyon,
        SUM(urun_sayisi) as toplam_urun
        FROM satislar 
        WHERE tarih BETWEEN ? AND ?");
    $stmt->execute([$start_date, $end_date]);
    $genel_stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Saatlik veriler (grafik için)
    $stmt = $conn->prepare("SELECT 
        HOUR(saat) as saat,
        COUNT(*) as adisyon_sayisi,
        SUM(toplam_tutar) as saatlik_ciro
        FROM satislar 
        WHERE tarih BETWEEN ? AND ?
        GROUP BY HOUR(saat)
        ORDER BY saat");
    $stmt->execute([$start_date, $end_date]);
    $saatlik_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // En çok satan ürünler
    $stmt = $conn->prepare("SELECT 
        sd.urun_adi,
        SUM(sd.miktar) as toplam_miktar,
        SUM(sd.toplam_fiyat) as toplam_gelir
        FROM satis_detaylari sd
        JOIN satislar s ON sd.satis_id = s.id
        WHERE s.tarih BETWEEN ? AND ?
        GROUP BY sd.urun_adi
        ORDER BY toplam_miktar DESC
        LIMIT 10");
    $stmt->execute([$start_date, $end_date]);
    $en_cok_satan = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Son satışlar
    $stmt = $conn->prepare("SELECT 
        adisyon_no,
        tarih,
        saat,
        toplam_tutar,
        urun_sayisi
        FROM satislar 
        WHERE tarih BETWEEN ? AND ?
        ORDER BY tarih DESC, saat DESC
        LIMIT 20");
    $stmt->execute([$start_date, $end_date]);
    $son_satislar = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $hata = "Veritabanı bağlantı hatası: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Satış Raporları - Tiryakideyim</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            background: #f5f5f5;
            color: #333;
            overflow-x: hidden;
            scroll-behavior: smooth;
        }
        
        .header {
            background: linear-gradient(135deg, #8B4513, #D2691E);
            color: white;
            padding: 15px 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            font-size: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logout-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
        }
        
        .container {
            display: flex;
            min-height: calc(100vh - 70px);
        }
        
        .sidebar {
            width: 250px;
            background: white;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            padding: 20px 0;
        }
        
        .sidebar ul {
            list-style: none;
        }
        
        .sidebar li {
            margin-bottom: 5px;
        }
        
        .sidebar a {
            display: block;
            padding: 15px 25px;
            color: #666;
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }
        
        .sidebar a:hover {
            background: #f8f9fa;
            color: #D2691E;
            border-left-color: #D2691E;
        }
        
        .sidebar a.active {
            background: #fff3e0;
            color: #D2691E;
            border-left-color: #D2691E;
            font-weight: 600;
        }
        
        .main-content {
            flex: 1;
            padding: 30px;
        }
        
        .page-header {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .page-header h2 {
            color: #8B4513;
            margin-bottom: 10px;
        }
        
        .filter-controls {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .filter-controls select,
        .filter-controls input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .filter-btn {
            background: #D2691E;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        
        .filter-btn:hover {
            background: #8B4513;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card.revenue {
            border-top: 4px solid #4CAF50;
        }
        
        .stat-card.orders {
            border-top: 4px solid #2196F3;
        }
        
        .stat-card.average {
            border-top: 4px solid #FF9800;
        }
        
        .stat-card.products {
            border-top: 4px solid #9C27B0;
        }
        
        .stat-number {
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .stat-card.revenue .stat-number {
            color: #4CAF50;
        }
        
        .stat-card.orders .stat-number {
            color: #2196F3;
        }
        
        .stat-card.average .stat-number {
            color: #FF9800;
        }
        
        .stat-card.products .stat-number {
            color: #9C27B0;
        }
        
        .stat-label {
            color: #666;
            font-size: 14px;
            font-weight: 500;
        }
        
        .stat-icon {
            font-size: 48px;
            margin-bottom: 15px;
            opacity: 0.7;
        }
        
        .reports-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .chart-container {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            height: 400px;
            overflow: hidden;
            position: relative;
        }
        
        .chart-container h3 {
            color: #8B4513;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .top-products {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .top-products h3 {
            color: #8B4513;
            margin-bottom: 20px;
        }
        
        .product-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .product-item:last-child {
            border-bottom: none;
        }
        
        .product-name {
            font-weight: 500;
        }
        
        .product-stats {
            text-align: right;
            font-size: 12px;
            color: #666;
        }
        
        .product-quantity {
            font-weight: bold;
            color: #D2691E;
        }
        
        .recent-sales {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .recent-sales h3 {
            color: #8B4513;
            margin-bottom: 20px;
        }
        
        .sales-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .sales-table th,
        .sales-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .sales-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #8B4513;
        }
        
        .sales-table tr:hover {
            background: #f8f9fa;
        }
        
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                order: 2;
            }
            
            .main-content {
                order: 1;
                padding: 20px;
            }
            
            .header {
                padding: 15px 20px;
            }
            
            .header h1 {
                font-size: 20px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .reports-grid {
                grid-template-columns: 1fr;
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>
            <span>📊</span>
            Satış Raporları
        </h1>
        <div class="user-info">
            <span>Hoş geldin, <?php echo htmlspecialchars($_SESSION['admin_adi']); ?></span>
            <a href="cikis.php" class="logout-btn">Çıkış Yap</a>
        </div>
    </div>
    
    <div class="container">
        <div class="sidebar">
            <ul>
                <li><a href="panel.php">📊 Dashboard</a></li>
                <li><a href="urunler.php">🍽️ Ürünler</a></li>
                <li><a href="kategoriler.php">📂 Kategoriler</a></li>
                <li><a href="adisyon.php">🧾 Adisyon</a></li>
                <li><a href="hesap.php">💳 Hesap Yönetimi</a></li>
                <li><a href="raporlar.php" class="active">📈 Raporlar</a></li>
                <li><a href="ayarlar.php">⚙️ Ayarlar</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <div class="page-header">
                <div>
                    <h2>Satış Raporları</h2>
                    <p>Kafenizin satış performansını analiz edin ve trendleri takip edin.</p>
                </div>
                
                <form method="GET" class="filter-controls">
                    <select name="filter" id="filter">
                        <option value="gunluk" <?php echo $filter == 'gunluk' ? 'selected' : ''; ?>>Günlük</option>
                        <option value="haftalik" <?php echo $filter == 'haftalik' ? 'selected' : ''; ?>>Haftalık</option>
                        <option value="aylik" <?php echo $filter == 'aylik' ? 'selected' : ''; ?>>Aylık</option>
                    </select>
                    <input type="date" name="tarih" value="<?php echo $tarih; ?>">
                    <button type="submit" class="filter-btn">Filtrele</button>
                </form>
            </div>
            
            <?php if (isset($hata)): ?>
                <div style="background: #ffebee; color: #c62828; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                    <?php echo htmlspecialchars($hata); ?>
                </div>
            <?php else: ?>
                <!-- Hesap Raporları Bölümü -->
                <?php
                // Açık hesap özeti
                $acik_hesap_stmt = $conn->prepare("
                    SELECT 
                        COUNT(*) as toplam_hesap,
                        SUM(mevcut_borc) as toplam_borc,
                        SUM(kredi_limiti - mevcut_borc) as toplam_limit
                    FROM acik_hesaplar 
                    WHERE durum = 'aktif'
                ");
                $acik_hesap_stmt->execute();
                $acik_hesap_ozet = $acik_hesap_stmt->fetch(PDO::FETCH_ASSOC);
                
                // Veresiye özeti
                $veresiye_stmt = $conn->prepare("
                    SELECT 
                        COUNT(*) as toplam_musteri,
                        SUM(mevcut_borc) as toplam_borc
                    FROM veresiye_musteriler 
                    WHERE durum = 'aktif'
                ");
                $veresiye_stmt->execute();
                $veresiye_ozet = $veresiye_stmt->fetch(PDO::FETCH_ASSOC);
                
                // Son hesap hareketleri
                $hesap_hareketleri_stmt = $conn->prepare("
                    SELECT 
                        hh.*, 
                        CASE 
                            WHEN hh.hesap_tipi = 'acik_hesap' THEN ah.hesap_adi
                            WHEN hh.hesap_tipi = 'veresiye' THEN vm.ad_soyad
                        END as hesap_adi
                    FROM hesap_hareketleri hh
                    LEFT JOIN acik_hesaplar ah ON hh.hesap_tipi = 'acik_hesap' AND hh.hesap_id = ah.id
                    LEFT JOIN veresiye_musteriler vm ON hh.hesap_tipi = 'veresiye' AND hh.hesap_id = vm.id
                    ORDER BY hh.tarih DESC, hh.saat DESC
                    LIMIT 10
                ");
                $hesap_hareketleri_stmt->execute();
                $hesap_hareketleri = $hesap_hareketleri_stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                
                <div class="stats-grid">
                    <div class="stat-card revenue">
                        <div class="stat-icon">💰</div>
                        <div class="stat-number"><?php echo number_format($genel_stats['toplam_ciro'] ?? 0, 2); ?> ₺</div>
                        <div class="stat-label">Toplam Ciro</div>
                    </div>
                    
                    <div class="stat-card orders">
                        <div class="stat-icon">🧾</div>
                        <div class="stat-number"><?php echo $genel_stats['toplam_adisyon'] ?? 0; ?></div>
                        <div class="stat-label">Toplam Adisyon</div>
                    </div>
                    
                    <div class="stat-card average">
                        <div class="stat-icon">📊</div>
                        <div class="stat-number"><?php echo number_format($genel_stats['ortalama_adisyon'] ?? 0, 2); ?> ₺</div>
                        <div class="stat-label">Ortalama Adisyon</div>
                    </div>
                    
                    <div class="stat-card products">
                        <div class="stat-icon">🍽️</div>
                        <div class="stat-number"><?php echo $genel_stats['toplam_urun'] ?? 0; ?></div>
                        <div class="stat-label">Satılan Ürün</div>
                    </div>
                </div>
                
                <!-- Hesap Özeti Kartları -->
                <div class="stats-grid">
                    <div class="stat-card revenue">
                        <div class="stat-icon">💳</div>
                        <div class="stat-number"><?php echo $acik_hesap_ozet['toplam_hesap'] ?: 0; ?></div>
                        <div class="stat-label">Açık Hesap</div>
                    </div>
                    
                    <div class="stat-card orders">
                        <div class="stat-icon">💰</div>
                        <div class="stat-number"><?php echo number_format($acik_hesap_ozet['toplam_borc'] ?: 0, 2); ?> ₺</div>
                        <div class="stat-label">Açık Hesap Borç</div>
                    </div>
                    
                    <div class="stat-card average">
                        <div class="stat-icon">👥</div>
                        <div class="stat-number"><?php echo $veresiye_ozet['toplam_musteri'] ?: 0; ?></div>
                        <div class="stat-label">Veresiye Müşteri</div>
                    </div>
                    
                    <div class="stat-card products">
                        <div class="stat-icon">💸</div>
                        <div class="stat-number"><?php echo number_format($veresiye_ozet['toplam_borc'] ?: 0, 2); ?> ₺</div>
                        <div class="stat-label">Veresiye Borç</div>
                    </div>
                </div>
                
                <div class="reports-grid">
                    <div class="chart-container">
                        <h3>Saatlik Satış Trendi</h3>
                        <canvas id="hourlyChart" width="400" height="200" style="max-width: 100%; max-height: 300px; display: block;"></canvas>
                    </div>
                    
                    <div class="top-products">
                        <h3>En Çok Satan Ürünler</h3>
                        <?php if (empty($en_cok_satan)): ?>
                            <p style="text-align: center; color: #666; padding: 20px;">Bu dönemde satış verisi bulunamadı.</p>
                        <?php else: ?>
                            <?php foreach ($en_cok_satan as $urun): ?>
                                <div class="product-item">
                                    <div class="product-name"><?php echo htmlspecialchars($urun['urun_adi']); ?></div>
                                    <div class="product-stats">
                                        <div class="product-quantity"><?php echo $urun['toplam_miktar']; ?> adet</div>
                                        <div><?php echo number_format($urun['toplam_gelir'], 2); ?> ₺</div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="recent-sales">
                    <h3>Son Satışlar</h3>
                    <?php if (empty($son_satislar)): ?>
                        <p style="text-align: center; color: #666; padding: 20px;">Bu dönemde satış verisi bulunamadı.</p>
                    <?php else: ?>
                        <table class="sales-table">
                            <thead>
                                <tr>
                                    <th>Adisyon No</th>
                                    <th>Tarih</th>
                                    <th>Saat</th>
                                    <th>Tutar</th>
                                    <th>Ürün Sayısı</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($son_satislar as $satis): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($satis['adisyon_no']); ?></td>
                                        <td><?php echo date('d.m.Y', strtotime($satis['tarih'])); ?></td>
                                        <td><?php echo date('H:i', strtotime($satis['saat'])); ?></td>
                                        <td><?php echo number_format($satis['toplam_tutar'], 2); ?> ₺</td>
                                        <td><?php echo $satis['urun_sayisi']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
                
                <!-- Hesap Hareketleri Bölümü -->
                <div class="recent-sales">
                    <h3>Son Hesap Hareketleri</h3>
                    <?php if (empty($hesap_hareketleri)): ?>
                        <p style="text-align: center; color: #666; padding: 20px;">Hesap hareketi bulunamadı.</p>
                    <?php else: ?>
                        <table class="sales-table">
                            <thead>
                                <tr>
                                    <th>Hesap Adı</th>
                                    <th>Tip</th>
                                    <th>İşlem</th>
                                    <th>Tutar</th>
                                    <th>Tarih</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($hesap_hareketleri as $hareket): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($hareket['hesap_adi']); ?></td>
                                        <td><?php echo $hareket['hesap_tipi'] == 'acik_hesap' ? 'Açık Hesap' : 'Veresiye'; ?></td>
                                        <td><?php echo ucfirst($hareket['islem_tipi']); ?></td>
                                        <td style="color: <?php echo $hareket['islem_tipi'] == 'borc' ? '#f44336' : '#4CAF50'; ?>">
                                            <?php echo number_format($hareket['tutar'], 2); ?> ₺
                                        </td>
                                        <td><?php echo date('d.m.Y H:i', strtotime($hareket['tarih'] . ' ' . $hareket['saat'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Saatlik satış grafiği
        const hourlyData = <?php echo json_encode($saatlik_data); ?>;
        
        // 24 saatlik veri hazırla
        const hours = Array.from({length: 24}, (_, i) => i);
        const salesData = hours.map(hour => {
            const found = hourlyData.find(item => parseInt(item.saat) === hour);
            return found ? parseFloat(found.saatlik_ciro) : 0;
        });
        
        const ctx = document.getElementById('hourlyChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: hours.map(h => h + ':00'),
                datasets: [{
                    label: 'Saatlik Ciro (₺)',
                    data: salesData,
                    borderColor: '#D2691E',
                    backgroundColor: 'rgba(210, 105, 30, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: false,
                layout: {
                    padding: 10
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value + ' ₺';
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    </script>
</body>
</html>