<?php
// Veritabanı bağlantı bilgileri
$servername = "localhost";
$username = "ymc15dmasycomtr_qrmenutk";
$password = "1b58b79a!A";
$dbname = "ymc15dmasycomtr_qrmenu";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Ayarları çek
    $ayarlar_raw = $conn->query("SELECT ayar_adi, ayar_degeri FROM Ayarlar")->fetchAll();
    $ayarlar = [];
    foreach ($ayarlar_raw as $ayar) {
        $ayarlar[$ayar['ayar_adi']] = $ayar['ayar_degeri'];
    }
    
    // Aktif tema bilgilerini çek
    $aktif_tema = null;
    if (isset($ayarlar['tema_adi'])) {
        $stmt = $conn->prepare("SELECT * FROM Temalar WHERE tema_adi = ?");
        $stmt->execute([$ayarlar['tema_adi']]);
        $aktif_tema = $stmt->fetch();
    }
    
    // Bakım modunu kontrol et
    if ($ayarlar && $ayarlar['bakim_modu']) {
        echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Bakım Modu</title></head><body style='font-family: Arial; text-align: center; padding: 50px;'><h1>🔧 Bakım Modu</h1><p>Menümüz şu anda güncelleniyor. Lütfen daha sonra tekrar deneyin.</p></body></html>";
        exit;
    }
    
    // Kategorileri çek (sadece görünür olanlar)
    $kategoriler = $conn->query("
        SELECT k.*, COUNT(u.id) as urun_sayisi 
        FROM Kategoriler k 
        LEFT JOIN Urunler u ON k.id = u.kategori_id AND u.gorunurluk = 1
        WHERE k.gorunurluk = 1 
        GROUP BY k.id 
        ORDER BY k.sira ASC, k.kategori_adi ASC
    ")->fetchAll();
    
    // Şefin spesiyallerini çek (ana sayfada göstermek için)
    $spesiyaller = $conn->query("
        SELECT u.*, k.kategori_adi 
        FROM Urunler u 
        LEFT JOIN Kategoriler k ON u.kategori_id = k.id 
        WHERE u.kafe_spesiyali = 1 AND u.gorunurluk = 1 
        ORDER BY u.sira ASC, u.urun_adi ASC 
        LIMIT 10
    ")->fetchAll();
    
    // Seçili kategori
    $secili_kategori_id = $_GET['kategori'] ?? null;
    $secili_urun_id = $_GET['urun'] ?? null;
    
    // Seçili kategorinin ürünlerini çek
    $urunler = [];
    $secili_kategori = null;
    if ($secili_kategori_id) {
        $stmt = $conn->prepare("
            SELECT * FROM Urunler 
            WHERE kategori_id = ? AND gorunurluk = 1 
            ORDER BY sira ASC, urun_adi ASC
        ");
        $stmt->execute([$secili_kategori_id]);
        $urunler = $stmt->fetchAll();
        
        // Kategori bilgisini al
        $stmt = $conn->prepare("SELECT * FROM Kategoriler WHERE id = ?");
        $stmt->execute([$secili_kategori_id]);
        $secili_kategori = $stmt->fetch();
    }
    
    // Seçili ürün detayı
    $secili_urun = null;
    if ($secili_urun_id) {
        $stmt = $conn->prepare("SELECT u.*, k.kategori_adi FROM Urunler u LEFT JOIN Kategoriler k ON u.kategori_id = k.id WHERE u.id = ? AND u.gorunurluk = 1");
        $stmt->execute([$secili_urun_id]);
        $secili_urun = $stmt->fetch();
    }
    
} catch(PDOException $e) {
    echo "Bağlantı hatası: " . $e->getMessage();
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $secili_urun ? htmlspecialchars($secili_urun['urun_adi']) . ' - ' : ''; ?><?php echo $secili_kategori ? htmlspecialchars($secili_kategori['kategori_adi']) . ' - ' : ''; ?>Tiryakideyim QR Menü</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&family=Dancing+Script:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: <?php echo $ayarlar['ana_renk'] ?? '#8B4513'; ?>;
            --secondary-color: <?php echo $ayarlar['ikincil_renk'] ?? '#D2691E'; ?>;
            --accent-color: <?php echo $ayarlar['vurgu_rengi'] ?? '#F4A460'; ?>;
            --text-color: <?php echo $ayarlar['metin_rengi'] ?? '#333'; ?>;
            --bg-color: <?php echo $ayarlar['arkaplan_rengi'] ?? '#fff'; ?>;
            --card-bg: #fff;
            --border-color: #e0e0e0;
            --shadow: 0 2px 10px rgba(0,0,0,0.1);
            --font-family: <?php echo $ayarlar['font_ailesi'] ?? 'Poppins, sans-serif'; ?>;
            --title-size: <?php echo $ayarlar['baslik_font_boyutu'] ?? '24'; ?>px;
            --text-size: <?php echo $ayarlar['metin_font_boyutu'] ?? '16'; ?>px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: var(--font-family);
            background: var(--bg-color);
            min-height: 100vh;
            color: var(--text-color);
            font-size: var(--text-size);
        }
        
        .header {
            background: var(--card-bg);
            padding: 20px;
            text-align: center;
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .header h1 {
            color: var(--primary-color);
            font-size: var(--title-size);
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .header .subtitle {
            color: #666;
            font-size: 14px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--card-bg);
            color: var(--primary-color);
            padding: 12px 20px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 500;
            box-shadow: var(--shadow);
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        
        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }
        
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .category-card {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 25px 15px;
            text-align: center;
            text-decoration: none;
            color: var(--text-color);
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .category-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        }
        
        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .category-icon {
            font-size: 48px;
            margin-bottom: 15px;
            display: block;
        }
        
        .category-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 50%;
            margin-bottom: 15px;
            border: 3px solid rgba(255,255,255,0.3);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        
        .category-name {
            font-size: 16px;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 8px;
        }
        
        .category-count {
            font-size: 12px;
            color: #666;
            background: #f5f5f5;
            padding: 4px 12px;
            border-radius: 15px;
            display: inline-block;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .product-card {
            background: var(--card-bg);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            text-decoration: none;
            color: var(--text-color);
        }
        
        .product-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        }
        
        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: linear-gradient(45deg, #f0f0f0, #e0e0e0);
        }
        
        .product-info {
            padding: 20px;
        }
        
        .product-name {
            font-size: 18px;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 8px;
        }
        
        .product-description {
            font-size: 14px;
            color: #666;
            margin-bottom: 15px;
            line-height: 1.4;
        }
        
        .product-price {
            font-size: 20px;
            font-weight: 700;
            color: var(--secondary-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .special-badge {
            background: linear-gradient(45deg, #FFD700, #FFA500);
            color: white;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
            position: absolute;
            top: 15px;
            right: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        
        .product-detail {
            background: var(--card-bg);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow);
            margin-top: 20px;
        }
        
        .product-detail-image {
            width: 100%;
            height: 300px;
            object-fit: cover;
        }
        
        .product-detail-info {
            padding: 30px;
        }
        
        .product-detail-name {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .product-detail-category {
            color: #666;
            font-size: 14px;
            margin-bottom: 20px;
        }
        
        .product-detail-description {
            font-size: 16px;
            line-height: 1.6;
            color: #555;
            margin-bottom: 25px;
        }
        
        .product-detail-price {
            font-size: 32px;
            font-weight: 700;
            color: var(--secondary-color);
            margin-bottom: 20px;
        }
        
        .allergen-info {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
        }
        
        .allergen-title {
            font-weight: 600;
            color: #856404;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .allergen-text {
            color: #856404;
            font-size: 14px;
        }
        
        .no-image {
            width: 100%;
            height: 200px;
            background: linear-gradient(45deg, #f8f9fa, #e9ecef);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            font-size: 48px;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .specials-banner {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }
        
        .specials-title {
            color: var(--primary-color);
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 15px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .specials-scroll {
            overflow-x: auto;
            overflow-y: hidden;
            white-space: nowrap;
            padding: 10px 0;
            scrollbar-width: thin;
            scrollbar-color: var(--primary-color) transparent;
            -webkit-overflow-scrolling: touch;
            position: relative;
            animation: autoScroll 20s linear infinite;
        }
        
        .specials-scroll:hover {
            animation-play-state: paused;
        }
        
        @keyframes autoScroll {
            0% { transform: translateX(0); }
            100% { transform: translateX(-33.33%); }
        }
        
        .specials-scroll {
            animation-duration: 30s;
        }
        
        .specials-scroll:hover .special-item {
            transform: scale(1.05);
        }
        
        .specials-scroll::-webkit-scrollbar {
            height: 6px;
        }
        
        .specials-scroll::-webkit-scrollbar-track {
            background: rgba(0,0,0,0.1);
            border-radius: 3px;
        }
        
        .specials-scroll::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 3px;
        }
        
        .specials-scroll::-webkit-scrollbar-thumb:hover {
            background: var(--secondary-color);
        }
        
        .specials-scroll {
            --scroll-indicator-opacity: 0.8;
        }
        
        .specials-scroll::after {
            content: '🔄 Otomatik Döner';
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0,0,0,0.7);
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            pointer-events: none;
            opacity: var(--scroll-indicator-opacity);
            animation: fadeInOut 4s infinite;
            transition: opacity 0.3s ease;
        }
        
        .specials-scroll:hover::after {
            content: '⏸️ Duraklatıldı';
            background: rgba(255,0,0,0.7);
        }
        
        @keyframes fadeInOut {
            0%, 100% { opacity: 0.3; }
            50% { opacity: 0.8; }
        }
        
        .special-item {
            display: inline-block;
            width: 200px;
            margin-right: 15px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 12px;
            padding: 15px;
            color: white;
            text-decoration: none;
            vertical-align: top;
            white-space: normal;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .special-item::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
            transform: rotate(45deg);
            transition: all 0.6s ease;
        }
        
        .special-item:hover::before {
            animation: shine 0.6s ease;
        }
        
        @keyframes shine {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }
        
        .special-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }
        
        .special-item-image {
            width: 100%;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        
        .special-item-no-image {
            width: 100%;
            height: 80px;
            background: rgba(255,255,255,0.2);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .special-item-name {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 5px;
            line-height: 1.2;
        }
        
        .special-item-category {
            font-size: 11px;
            opacity: 0.8;
            margin-bottom: 8px;
        }
        
        .special-item-price {
            font-size: 16px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .special-star {
            font-size: 18px;
            animation: sparkle 2s infinite;
        }
        
        @keyframes sparkle {
            0%, 100% { transform: scale(1) rotate(0deg); }
            50% { transform: scale(1.1) rotate(180deg); }
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            .categories-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
            }
            
            .products-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .category-card {
                padding: 20px 10px;
            }
            
            .category-icon {
                font-size: 32px;
            }
            
            .category-image {
                width: 60px;
                height: 60px;
            }
            
            .category-name {
                font-size: 14px;
            }
            
            .product-detail-info {
                padding: 20px;
            }
            
            .product-detail-name {
                font-size: 24px;
            }
            
            .product-detail-price {
                font-size: 28px;
            }
            
            .specials-banner {
                margin-bottom: 20px;
                padding: 15px;
            }
            
            .specials-title {
                font-size: 18px;
            }
            
            .special-item {
                width: 160px;
                padding: 12px;
            }
            
            .special-item-image,
            .special-item-no-image {
                height: 60px;
            }
            
            .special-item-no-image {
                font-size: 24px;
            }
            
            .specials-scroll::after {
                content: '👉';
                font-size: 16px;
                padding: 8px;
                right: 5px;
            }
            
            .specials-scroll {
                scrollbar-width: auto;
            }
            
            .specials-scroll::-webkit-scrollbar {
                height: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>☕ Tiryakideyim</h1>
        <div class="subtitle">Lezzetin Adresi</div>
    </div>
    
    <div class="container">
        <?php if ($secili_urun): ?>
            <!-- Ürün Detay Sayfası -->
            <a href="?kategori=<?php echo $secili_urun['kategori_id']; ?>" class="back-btn">
                ← <?php echo htmlspecialchars($secili_urun['kategori_adi']); ?> Kategorisine Dön
            </a>
            
            <div class="product-detail">
                <?php if (!empty($secili_urun['gorsel_url'])): ?>
                    <img src="<?php echo htmlspecialchars($secili_urun['gorsel_url']); ?>" 
                         alt="<?php echo htmlspecialchars($secili_urun['urun_adi']); ?>" 
                         class="product-detail-image">
                <?php else: ?>
                    <div class="no-image">🍽️</div>
                <?php endif; ?>
                
                <?php if ($secili_urun['kafe_spesiyali']): ?>
                    <div class="special-badge">⭐ Spesiyal</div>
                <?php endif; ?>
                
                <div class="product-detail-info">
                    <h2 class="product-detail-name"><?php echo htmlspecialchars($secili_urun['urun_adi']); ?></h2>
                    <div class="product-detail-category"><?php echo htmlspecialchars($secili_urun['kategori_adi']); ?></div>
                    
                    <?php if (!empty($secili_urun['aciklama'])): ?>
                        <div class="product-detail-description">
                            <?php echo nl2br(htmlspecialchars($secili_urun['aciklama'])); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="product-detail-price">
                        <?php echo number_format($secili_urun['fiyat'], 2); ?> TL
                    </div>
                    
                    <?php if (!empty($secili_urun['alerji_uyari'])): ?>
                        <div class="allergen-info">
                            <div class="allergen-title">
                                ⚠️ Alerji Uyarısı
                            </div>
                            <div class="allergen-text">
                                <?php echo htmlspecialchars($secili_urun['alerji_uyari']); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
        <?php elseif ($secili_kategori): ?>
            <!-- Kategori Ürünleri Sayfası -->
            <a href="qrmenu.php" class="back-btn">
                ← Ana Menüye Dön
            </a>
            
            <h2 style="color: var(--primary-color); font-size: 24px; margin-bottom: 20px; text-align: center;">
                📂 <?php echo htmlspecialchars($secili_kategori['kategori_adi']); ?>
            </h2>
            
            <?php if (!empty($urunler)): ?>
                <div class="products-grid">
                    <?php foreach ($urunler as $urun): ?>
                        <a href="?urun=<?php echo $urun['id']; ?>" class="product-card">
                            <?php if (!empty($urun['gorsel_url'])): ?>
                                <img src="<?php echo htmlspecialchars($urun['gorsel_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($urun['urun_adi']); ?>" 
                                     class="product-image">
                            <?php else: ?>
                                <div class="no-image">🍽️</div>
                            <?php endif; ?>
                            
                            <?php if ($urun['kafe_spesiyali']): ?>
                                <div class="special-badge">⭐ Spesiyal</div>
                            <?php endif; ?>
                            
                            <div class="product-info">
                                <div class="product-name"><?php echo htmlspecialchars($urun['urun_adi']); ?></div>
                                
                                <?php if (!empty($urun['aciklama'])): ?>
                                    <div class="product-description">
                                        <?php echo htmlspecialchars(substr($urun['aciklama'], 0, 80)) . (strlen($urun['aciklama']) > 80 ? '...' : ''); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="product-price">
                                    <?php echo number_format($urun['fiyat'], 2); ?> TL
                                    <span style="font-size: 14px; color: #666;">Detay →</span>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">🍽️</div>
                    <h3>Bu kategoride henüz ürün bulunmuyor</h3>
                    <p>Yakında lezzetli ürünlerle karşınızda olacağız!</p>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <!-- Ana Kategori Sayfası -->
            
            <!-- Şefin Spesiyalleri Banner -->
            <?php if (!empty($spesiyaller)): ?>
                <div class="specials-banner">
                    <div class="specials-title">
                        <span class="special-star">⭐</span>
                        Şefin Spesiyalleri
                        <span class="special-star">⭐</span>
                    </div>
                    <div class="specials-scroll">
                        <?php 
                        // Sonsuz döngü için ürünleri 3 kez tekrarla
                        for ($i = 0; $i < 3; $i++): 
                            foreach ($spesiyaller as $spesiyal): 
                        ?>
                            <a href="?urun=<?php echo $spesiyal['id']; ?>" class="special-item">
                                <?php if (!empty($spesiyal['gorsel_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($spesiyal['gorsel_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($spesiyal['urun_adi']); ?>" 
                                         class="special-item-image">
                                <?php else: ?>
                                    <div class="special-item-no-image">🍽️</div>
                                <?php endif; ?>
                                
                                <div class="special-item-name"><?php echo htmlspecialchars($spesiyal['urun_adi']); ?></div>
                                <div class="special-item-category"><?php echo htmlspecialchars($spesiyal['kategori_adi']); ?></div>
                                <div class="special-item-price">
                                    <?php echo number_format($spesiyal['fiyat'], 2); ?> TL
                                    <span>👨‍🍳</span>
                                </div>
                            </a>
                        <?php 
                            endforeach; 
                        endfor; 
                        ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <h2 style="color: white; font-size: 24px; margin-bottom: 20px; text-align: center; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">
                🍽️ Menü Kategorileri
            </h2>
            
            <?php if (!empty($kategoriler)): ?>
                <div class="categories-grid">
                    <?php 
                    $kategori_iconlari = [
                        'kahve' => '☕', 'coffee' => '☕', 'espresso' => '☕',
                        'yiyecek' => '🍽️', 'yemek' => '🍽️', 'food' => '🍽️',
                        'içecek' => '🥤', 'drink' => '🥤', 'beverage' => '🥤',
                        'nargile' => '💨', 'hookah' => '💨', 'shisha' => '💨',
                        'tatlı' => '🍰', 'dessert' => '🍰', 'sweet' => '🍰',
                        'toplu' => '🍱', 'combo' => '🍱', 'set' => '🍱',
                        'çay' => '🍵', 'tea' => '🍵',
                        'pasta' => '🍰', 'cake' => '🍰',
                        'sandviç' => '🥪', 'sandwich' => '🥪',
                        'salata' => '🥗', 'salad' => '🥗',
                        'pizza' => '🍕', 'burger' => '🍔',
                        'dondurma' => '🍦', 'ice cream' => '🍦'
                    ];
                    
                    foreach ($kategoriler as $kategori): 
                        $icon = '📂';
                        $kategori_adi_lower = strtolower($kategori['kategori_adi']);
                        foreach ($kategori_iconlari as $anahtar => $simge) {
                            if (strpos($kategori_adi_lower, $anahtar) !== false) {
                                $icon = $simge;
                                break;
                            }
                        }
                    ?>
                        <a href="?kategori=<?php echo $kategori['id']; ?>" class="category-card">
                            <?php if (!empty($kategori['gorsel_url'])): ?>
                                <img src="<?php echo htmlspecialchars($kategori['gorsel_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($kategori['kategori_adi']); ?>" 
                                     class="category-image">
                            <?php else: ?>
                                <span class="category-icon"><?php echo $icon; ?></span>
                            <?php endif; ?>
                            <div class="category-name"><?php echo htmlspecialchars($kategori['kategori_adi']); ?></div>
                            <div class="category-count"><?php echo $kategori['urun_sayisi']; ?> ürün</div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📂</div>
                    <h3>Henüz kategori bulunmuyor</h3>
                    <p>Yakında lezzetli kategorilerle karşınızda olacağız!</p>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <?php if ($aktif_tema && !empty($aktif_tema['ozel_css'])): ?>
    <style>
        /* Tema Özel Stilleri: <?php echo htmlspecialchars($aktif_tema['tema_baslik']); ?> */
        <?php echo $aktif_tema['ozel_css']; ?>
    </style>
    <?php endif; ?>
    
    <?php if ($aktif_tema && !empty($aktif_tema['animasyon_css'])): ?>
    <style>
        /* Tema Animasyonları: <?php echo htmlspecialchars($aktif_tema['tema_baslik']); ?> */
        <?php echo $aktif_tema['animasyon_css']; ?>
    </style>
    <?php endif; ?>
    
    <style>
        /* Genel Tema Geçiş Animasyonları */
        * {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }
        
        body {
            animation: pageLoad 0.8s ease-out;
        }
        
        @keyframes pageLoad {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .header {
            animation: slideDown 0.6s ease-out;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .categories-grid {
            animation: fadeInUp 0.8s ease-out 0.2s both;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .category-card {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
            animation: cardAppear 0.6s ease-out both;
        }
        
        .category-card:nth-child(1) { animation-delay: 0.1s; }
        .category-card:nth-child(2) { animation-delay: 0.2s; }
        .category-card:nth-child(3) { animation-delay: 0.3s; }
        .category-card:nth-child(4) { animation-delay: 0.4s; }
        .category-card:nth-child(5) { animation-delay: 0.5s; }
        .category-card:nth-child(6) { animation-delay: 0.6s; }
        
        @keyframes cardAppear {
            from {
                opacity: 0;
                transform: translateY(20px) scale(0.9);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        
        .category-card:hover {
            transform: translateY(-8px) scale(1.05) !important;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2) !important;
        }
        
        .category-card:active {
            transform: translateY(-4px) scale(1.02) !important;
        }
        
        .back-button {
            transition: all 0.3s ease !important;
            animation: slideInLeft 0.5s ease-out;
        }
        
        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .back-button:hover {
            transform: translateX(-5px) scale(1.05) !important;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2) !important;
        }
        
        .empty-state {
            animation: bounceIn 1s ease-out 0.5s both;
        }
        
        @keyframes bounceIn {
            0% {
                opacity: 0;
                transform: scale(0.3);
            }
            50% {
                opacity: 1;
                transform: scale(1.1);
            }
            100% {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        /* Tema değişikliği için smooth geçiş */
        .theme-transition {
            animation: themeChange 0.5s ease-in-out;
        }
        
        @keyframes themeChange {
            0% { opacity: 1; }
            50% { opacity: 0.7; transform: scale(0.98); }
            100% { opacity: 1; transform: scale(1); }
        }
        
        /* Responsive animasyonlar */
        @media (max-width: 768px) {
            .category-card:hover {
                transform: translateY(-4px) scale(1.02) !important;
            }
        }
        
        /* Özel tema efektleri için hazır sınıflar */
        .glow-effect {
            box-shadow: 0 0 20px rgba(var(--primary-rgb), 0.3) !important;
        }
        
        .pulse-effect {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .shake-effect:hover {
            animation: shake 0.5s ease-in-out;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        
        .rotate-effect:hover {
            transform: rotate(5deg) scale(1.05) !important;
        }
        
        .flip-effect:hover {
            transform: rotateY(10deg) scale(1.05) !important;
        }
    </style>
    
    <script>
        // Tema değişikliği animasyonu
        function applyThemeTransition() {
            document.body.classList.add('theme-transition');
            setTimeout(() => {
                document.body.classList.remove('theme-transition');
            }, 500);
        }
        
        // Sayfa yüklendiğinde animasyonları başlat
        document.addEventListener('DOMContentLoaded', function() {
            // Kategori kartlarına sıralı animasyon ekle
            const cards = document.querySelectorAll('.category-card');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
            });
            
            // Tema değişikliği kontrolü (localStorage ile)
            const currentTheme = '<?php echo $aktif_tema['tema_adi'] ?? 'modern_minimalist'; ?>';
            const lastTheme = localStorage.getItem('lastTheme');
            
            if (lastTheme && lastTheme !== currentTheme) {
                applyThemeTransition();
            }
            
            localStorage.setItem('lastTheme', currentTheme);
        });
        
        // Kategori kartlarına tıklama efekti
        document.querySelectorAll('.category-card').forEach(card => {
            card.addEventListener('click', function(e) {
                // Ripple efekti
                const ripple = document.createElement('div');
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;
                
                ripple.style.cssText = `
                    position: absolute;
                    width: ${size}px;
                    height: ${size}px;
                    left: ${x}px;
                    top: ${y}px;
                    background: rgba(255,255,255,0.3);
                    border-radius: 50%;
                    transform: scale(0);
                    animation: ripple 0.6s ease-out;
                    pointer-events: none;
                `;
                
                this.style.position = 'relative';
                this.style.overflow = 'hidden';
                this.appendChild(ripple);
                
                setTimeout(() => {
                    ripple.remove();
                }, 600);
            });
        });
        
        // Ripple animasyonu
        const rippleStyle = document.createElement('style');
        rippleStyle.textContent = `
            @keyframes ripple {
                to {
                    transform: scale(2);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(rippleStyle);
        
        // Şefin spesiyalleri scroll desteği
        const specialsScroll = document.querySelector('.specials-scroll');
        if (specialsScroll) {
            let isScrolling = false;
            let scrollTimeout;
            
            // Scroll göstergesini gizle/göster
            function toggleScrollIndicator() {
                const indicator = specialsScroll.querySelector('::after');
                if (specialsScroll.scrollLeft > 0) {
                    specialsScroll.style.setProperty('--scroll-indicator-opacity', '0');
                } else {
                    specialsScroll.style.setProperty('--scroll-indicator-opacity', '0.8');
                }
            }
            
            // Scroll olayları
            specialsScroll.addEventListener('scroll', function() {
                isScrolling = true;
                
                // Scroll göstergesini gizle
                this.style.setProperty('--scroll-indicator-opacity', '0');
                
                clearTimeout(scrollTimeout);
                scrollTimeout = setTimeout(() => {
                    isScrolling = false;
                    // 2 saniye sonra göstergeyi tekrar göster (eğer başta ise)
                    if (this.scrollLeft === 0) {
                        this.style.setProperty('--scroll-indicator-opacity', '0.8');
                    }
                }, 2000);
            });
            
            // Touch desteği
            let startX = 0;
            let scrollLeft = 0;
            
            specialsScroll.addEventListener('touchstart', function(e) {
                startX = e.touches[0].pageX - this.offsetLeft;
                scrollLeft = this.scrollLeft;
            });
            
            specialsScroll.addEventListener('touchmove', function(e) {
                e.preventDefault();
                const x = e.touches[0].pageX - this.offsetLeft;
                const walk = (x - startX) * 2;
                this.scrollLeft = scrollLeft - walk;
            });
            
            // Mouse wheel horizontal scroll
            specialsScroll.addEventListener('wheel', function(e) {
                if (e.deltaY !== 0) {
                    e.preventDefault();
                    this.scrollLeft += e.deltaY;
                }
            });
        }
    </script>
</body>
</html>