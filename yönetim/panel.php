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
    
    // İstatistikleri çek
    $toplam_urun = $conn->query("SELECT COUNT(*) FROM Urunler")->fetchColumn();
    $toplam_kategori = $conn->query("SELECT COUNT(*) FROM Kategoriler")->fetchColumn();
    $gorunur_urun = $conn->query("SELECT COUNT(*) FROM Urunler WHERE gorunurluk = 1")->fetchColumn();
    $gorunur_kategori = $conn->query("SELECT COUNT(*) FROM Kategoriler WHERE gorunurluk = 1")->fetchColumn();
    
} catch(PDOException $e) {
    $hata = "Veritabanı bağlantı hatası: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tiryakideyim - Yönetim Paneli</title>
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
        
        .welcome-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .welcome-section h2 {
            color: #8B4513;
            margin-bottom: 10px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
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
        
        .stat-card.products {
            border-top: 4px solid #4CAF50;
        }
        
        .stat-card.categories {
            border-top: 4px solid #2196F3;
        }
        
        .stat-card.visible-products {
            border-top: 4px solid #FF9800;
        }
        
        .stat-card.visible-categories {
            border-top: 4px solid #9C27B0;
        }
        
        .stat-number {
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .stat-card.products .stat-number {
            color: #4CAF50;
        }
        
        .stat-card.categories .stat-number {
            color: #2196F3;
        }
        
        .stat-card.visible-products .stat-number {
            color: #FF9800;
        }
        
        .stat-card.visible-categories .stat-number {
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
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>
            <span>☕</span>
            Tiryakideyim Yönetim Paneli
        </h1>
        <div class="user-info">
            <span>Hoş geldin, <?php echo htmlspecialchars($_SESSION['admin_adi']); ?></span>
            <a href="cikis.php" class="logout-btn">Çıkış Yap</a>
        </div>
    </div>
    
    <div class="container">
        <div class="sidebar">
            <ul>
                <li><a href="panel.php" class="active">📊 Dashboard</a></li>
                <li><a href="urunler.php">🍽️ Ürünler</a></li>
                <li><a href="kategoriler.php">📂 Kategoriler</a></li>
                <li><a href="adisyon.php">🧾 Adisyon</a></li>
                <li><a href="raporlar.php">📈 Raporlar</a></li>
                <li><a href="ayarlar.php">⚙️ Ayarlar</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <div class="welcome-section">
                <h2>Dashboard</h2>
                <p>Tiryakideyim QR Menü yönetim paneline hoş geldiniz. Aşağıda kafenizin genel istatistiklerini görebilirsiniz.</p>
            </div>
            
            <?php if (isset($hata)): ?>
                <div style="background: #ffebee; color: #c62828; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                    <?php echo htmlspecialchars($hata); ?>
                </div>
            <?php else: ?>
                <div class="stats-grid">
                    <div class="stat-card products">
                        <div class="stat-icon">🍽️</div>
                        <div class="stat-number"><?php echo $toplam_urun; ?></div>
                        <div class="stat-label">Toplam Ürün</div>
                    </div>
                    
                    <div class="stat-card categories">
                        <div class="stat-icon">📂</div>
                        <div class="stat-number"><?php echo $toplam_kategori; ?></div>
                        <div class="stat-label">Toplam Kategori</div>
                    </div>
                    
                    <div class="stat-card visible-products">
                        <div class="stat-icon">👁️</div>
                        <div class="stat-number"><?php echo $gorunur_urun; ?></div>
                        <div class="stat-label">Görünür Ürün</div>
                    </div>
                    
                    <div class="stat-card visible-categories">
                        <div class="stat-icon">📋</div>
                        <div class="stat-number"><?php echo $gorunur_kategori; ?></div>
                        <div class="stat-label">Görünür Kategori</div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>