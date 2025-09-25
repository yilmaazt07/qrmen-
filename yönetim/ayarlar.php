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

$mesaj = "";
$hata = "";

// Google Fonts listesi
$google_fonts = [
    'Arial, sans-serif' => 'Arial',
    'Helvetica, sans-serif' => 'Helvetica',
    'Georgia, serif' => 'Georgia',
    'Times New Roman, serif' => 'Times New Roman',
    'Verdana, sans-serif' => 'Verdana',
    'Trebuchet MS, sans-serif' => 'Trebuchet MS',
    'Impact, sans-serif' => 'Impact',
    'Comic Sans MS, cursive' => 'Comic Sans MS',
    'Courier New, monospace' => 'Courier New',
    'Lucida Console, monospace' => 'Lucida Console',
    'Roboto, sans-serif' => 'Roboto (Google)',
    'Open Sans, sans-serif' => 'Open Sans (Google)',
    'Lato, sans-serif' => 'Lato (Google)',
    'Montserrat, sans-serif' => 'Montserrat (Google)',
    'Source Sans Pro, sans-serif' => 'Source Sans Pro (Google)',
    'Raleway, sans-serif' => 'Raleway (Google)',
    'Ubuntu, sans-serif' => 'Ubuntu (Google)',
    'Nunito, sans-serif' => 'Nunito (Google)',
    'Poppins, sans-serif' => 'Poppins (Google)',
    'Merriweather, serif' => 'Merriweather (Google)',
    'Playfair Display, serif' => 'Playfair Display (Google)',
    'Lora, serif' => 'Lora (Google)',
    'Dancing Script, cursive' => 'Dancing Script (Google)',
    'Pacifico, cursive' => 'Pacifico (Google)'
];

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Tema sistemi kurulumu (sadece bir kez çalışır)
    $stmt = $conn->prepare("SHOW TABLES LIKE 'Temalar'");
    $stmt->execute();
    if ($stmt->rowCount() == 0) {
        // Temalar tablosunu oluştur
        $sql = "CREATE TABLE IF NOT EXISTS Temalar (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tema_adi VARCHAR(50) NOT NULL UNIQUE,
            tema_baslik VARCHAR(100) NOT NULL,
            kategori VARCHAR(50) DEFAULT 'Genel',
            aciklama TEXT,
            ana_renk VARCHAR(7) NOT NULL,
            ikincil_renk VARCHAR(7) NOT NULL,
            arkaplan_rengi VARCHAR(7) NOT NULL,
            metin_rengi VARCHAR(7) NOT NULL,
            font_ailesi VARCHAR(100) NOT NULL,
            baslik_font_boyutu INT NOT NULL,
            metin_font_boyutu INT NOT NULL,
            ozel_css TEXT,
            animasyon_css TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $conn->exec($sql);
        
        // Kategori alanını mevcut tabloya ekle (eğer yoksa)
        $conn->exec("ALTER TABLE Temalar ADD COLUMN IF NOT EXISTS kategori VARCHAR(50) DEFAULT 'Genel'");
        $conn->exec("ALTER TABLE Temalar ADD COLUMN IF NOT EXISTS animasyon_css TEXT");
        $conn->exec($sql);
        
        // tema_adi ayarını ekle
        $stmt = $conn->prepare("SELECT COUNT(*) FROM Ayarlar WHERE ayar_adi = 'tema_adi'");
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            $stmt = $conn->prepare("INSERT INTO Ayarlar (ayar_adi, ayar_degeri, aciklama, kategori) VALUES ('tema_adi', 'modern_minimalist', 'Aktif tema adı', 'tema')");
            $stmt->execute();
        }
        
        // Varsayılan temaları ekle
        $temalar_data = [
            // Modern Temalar
            ['modern_minimalist', 'Modern Minimalist', 'Modern', 'Sade ve modern tasarım, temiz çizgiler', '#2C3E50', '#3498DB', '#FFFFFF', '#2C3E50', 'Roboto, sans-serif', 28, 16, '.category-card { border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); } .product-card { border: none; border-radius: 12px; }', '.category-card:hover { transform: translateY(-5px); transition: all 0.3s ease; }'],
            ['glassmorphism', 'Glassmorphism', 'Modern', 'Cam efekti ile modern görünüm', '#1E3A8A', '#3B82F6', 'rgba(255,255,255,0.1)', '#FFFFFF', 'Inter, sans-serif', 30, 17, '.category-card { background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.2); border-radius: 20px; } .product-card { background: rgba(255,255,255,0.1); backdrop-filter: blur(8px); }', '.category-card:hover { background: rgba(255,255,255,0.25); transform: scale(1.02); transition: all 0.4s ease; }'],
            ['neon_cyber', 'Neon Cyber', 'Modern', 'Cyberpunk tarzı neon efektler', '#0F0F23', '#00FFFF', '#1A1A2E', '#00FFFF', 'Orbitron, monospace', 32, 18, '.category-card { background: linear-gradient(145deg, #16213E, #0F3460); border: 2px solid #00FFFF; border-radius: 10px; box-shadow: 0 0 20px rgba(0,255,255,0.3); } .product-card { background: #16213E; border-left: 4px solid #E94560; }', '.category-card:hover { box-shadow: 0 0 30px rgba(0,255,255,0.6); animation: neonPulse 2s infinite; } @keyframes neonPulse { 0%, 100% { box-shadow: 0 0 20px rgba(0,255,255,0.3); } 50% { box-shadow: 0 0 40px rgba(0,255,255,0.8); } }'],
            
            // Klasik Temalar
            ['klasik_restoran', 'Klasik Restoran', 'Klasik', 'Geleneksel restoran atmosferi, sıcak renkler', '#8B4513', '#D2691E', '#FFF8DC', '#4A4A4A', 'Playfair Display, serif', 32, 18, '.category-card { background: linear-gradient(145deg, #FFF8DC, #F5DEB3); border: 2px solid #D2691E; } .product-card { background: #FFFAF0; border-left: 4px solid #D2691E; }', '.category-card:hover { transform: rotateY(5deg); transition: all 0.5s ease; }'],
            ['vintage_kahvehane', 'Vintage Kahvehane', 'Klasik', 'Nostaljik kahvehane teması, vintage dokular', '#5D4037', '#8D6E63', '#F3E5AB', '#3E2723', 'Dancing Script, cursive', 36, 17, '.category-card { background: radial-gradient(circle, #F3E5AB, #E6D7A3); border: 3px dashed #8D6E63; } .product-card { background: #FFF9C4; box-shadow: inset 0 0 10px rgba(139,69,19,0.1); }', '.category-card:hover { animation: vintageShake 0.5s ease-in-out; } @keyframes vintageShake { 0%, 100% { transform: rotate(0deg); } 25% { transform: rotate(1deg); } 75% { transform: rotate(-1deg); } }'],
            ['ottoman_saray', 'Osmanlı Sarayı', 'Klasik', 'Osmanlı saray teması, altın detaylar', '#8B0000', '#FFD700', '#F5F5DC', '#8B0000', 'Amiri, serif', 34, 19, '.category-card { background: linear-gradient(135deg, #F5F5DC, #E6E6FA); border: 3px solid #FFD700; border-radius: 15px; position: relative; } .category-card::before { content: ""; position: absolute; top: -2px; left: -2px; right: -2px; bottom: -2px; background: linear-gradient(45deg, #FFD700, #FFA500); border-radius: 17px; z-index: -1; }', '.category-card:hover { animation: royalGlow 1s ease-in-out infinite alternate; } @keyframes royalGlow { from { box-shadow: 0 0 10px rgba(255,215,0,0.5); } to { box-shadow: 0 0 25px rgba(255,215,0,0.8); } }'],
            
            // Mevsimsel Temalar
            ['yilbasi_kar', 'Yılbaşı Kar Tanesi', 'Mevsimsel', 'Kar taneleri ve yılbaşı atmosferi', '#B22222', '#228B22', '#F0F8FF', '#8B0000', 'Mountains of Christmas, cursive', 36, 18, '.category-card { background: linear-gradient(135deg, #F0F8FF, #E6F3FF); border: 2px solid #B22222; border-radius: 20px; position: relative; overflow: hidden; } .category-card::before { content: "❄"; position: absolute; top: 10px; right: 15px; font-size: 24px; color: #87CEEB; animation: snowfall 3s ease-in-out infinite; }', '.category-card:hover { animation: christmasShine 1s ease-in-out; } @keyframes christmasShine { 0% { background: linear-gradient(135deg, #F0F8FF, #E6F3FF); } 50% { background: linear-gradient(135deg, #FFE4E1, #F0F8FF); } 100% { background: linear-gradient(135deg, #F0F8FF, #E6F3FF); } } @keyframes snowfall { 0%, 100% { transform: translateY(0px) rotate(0deg); } 50% { transform: translateY(-10px) rotate(180deg); } }'],
            ['sonbahar_yaprak', 'Sonbahar Yaprakları', 'Mevsimsel', 'Sonbahar renkleri ve yaprak motifleri', '#8B4513', '#FF8C00', '#FFF8DC', '#654321', 'Crimson Text, serif', 32, 17, '.category-card { background: linear-gradient(45deg, #FFF8DC, #FFEFD5, #FFE4B5); border: 2px solid #D2691E; border-radius: 15px; position: relative; } .category-card::after { content: "🍂"; position: absolute; bottom: 10px; right: 15px; font-size: 20px; animation: leafFall 4s ease-in-out infinite; }', '.category-card:hover { animation: autumnWave 0.6s ease-in-out; } @keyframes autumnWave { 0%, 100% { transform: rotate(0deg); } 25% { transform: rotate(2deg); } 75% { transform: rotate(-2deg); } } @keyframes leafFall { 0%, 100% { transform: translateY(0px) rotate(0deg); } 50% { transform: translateY(5px) rotate(15deg); } }'],
            ['bahar_cicek', 'Bahar Çiçekleri', 'Mevsimsel', 'Bahar renkleri ve çiçek motifleri', '#32CD32', '#FF69B4', '#F0FFF0', '#228B22', 'Quicksand, sans-serif', 30, 16, '.category-card { background: linear-gradient(135deg, #F0FFF0, #E0FFE0); border: 2px solid #32CD32; border-radius: 25px; position: relative; } .category-card::before { content: "🌸"; position: absolute; top: 15px; left: 15px; font-size: 22px; animation: bloom 2s ease-in-out infinite; }', '.category-card:hover { animation: springBounce 0.5s ease-in-out; } @keyframes springBounce { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.05); } } @keyframes bloom { 0%, 100% { transform: scale(1) rotate(0deg); } 50% { transform: scale(1.2) rotate(10deg); } }'],
            ['yaz_gunes', 'Yaz Güneşi', 'Mevsimsel', 'Yaz sıcaklığı ve güneş ışığı', '#FFD700', '#FF6347', '#FFFACD', '#B8860B', 'Pacifico, cursive', 34, 18, '.category-card { background: radial-gradient(circle, #FFFACD, #FFFFE0); border: 3px solid #FFD700; border-radius: 20px; box-shadow: 0 0 15px rgba(255,215,0,0.4); } .product-card { background: linear-gradient(135deg, #FFFACD, #F0E68C); }', '.category-card:hover { animation: sunGlow 1s ease-in-out infinite alternate; } @keyframes sunGlow { from { box-shadow: 0 0 15px rgba(255,215,0,0.4); } to { box-shadow: 0 0 30px rgba(255,215,0,0.8); } }'],
            
            // Özel Günler
            ['sevgililer_gunu', 'Sevgililer Günü', 'Özel Günler', 'Romantik pembe ve kırmızı tonlar', '#DC143C', '#FF1493', '#FFF0F5', '#8B008B', 'Great Vibes, cursive', 38, 19, '.category-card { background: linear-gradient(135deg, #FFF0F5, #FFE4E6); border: 2px solid #FF1493; border-radius: 25px; position: relative; } .category-card::before { content: "💖"; position: absolute; top: 10px; right: 10px; font-size: 24px; animation: heartBeat 1.5s ease-in-out infinite; }', '.category-card:hover { animation: loveFloat 0.8s ease-in-out; } @keyframes loveFloat { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-8px); } } @keyframes heartBeat { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.3); } }'],
            ['halloween', 'Halloween', 'Özel Günler', 'Korku teması, turuncu ve siyah', '#FF4500', '#8B008B', '#2F2F2F', '#FF6347', 'Creepster, cursive', 36, 18, '.category-card { background: linear-gradient(135deg, #2F2F2F, #4A4A4A); border: 3px solid #FF4500; border-radius: 15px; box-shadow: 0 0 20px rgba(255,69,0,0.3); } .category-card::after { content: "🎃"; position: absolute; bottom: 10px; right: 10px; font-size: 28px; animation: spookyGlow 2s ease-in-out infinite; }', '.category-card:hover { animation: spookyShake 0.5s ease-in-out; } @keyframes spookyShake { 0%, 100% { transform: rotate(0deg); } 25% { transform: rotate(-2deg); } 75% { transform: rotate(2deg); } } @keyframes spookyGlow { 0%, 100% { filter: brightness(1); } 50% { filter: brightness(1.5) drop-shadow(0 0 10px #FF4500); } }'],
            ['ramazan', 'Ramazan', 'Özel Günler', 'İslami motifler, altın ve yeşil', '#228B22', '#FFD700', '#F5F5DC', '#006400', 'Amiri, serif', 34, 18, '.category-card { background: linear-gradient(135deg, #F5F5DC, #F0F8E8); border: 2px solid #228B22; border-radius: 20px; position: relative; } .category-card::before { content: "🌙"; position: absolute; top: 15px; right: 15px; font-size: 24px; color: #FFD700; animation: crescentGlow 3s ease-in-out infinite; }', '.category-card:hover { animation: blessedGlow 1s ease-in-out; } @keyframes blessedGlow { 0%, 100% { box-shadow: 0 0 10px rgba(34,139,34,0.3); } 50% { box-shadow: 0 0 25px rgba(255,215,0,0.6); } } @keyframes crescentGlow { 0%, 100% { transform: rotate(0deg) scale(1); } 50% { transform: rotate(15deg) scale(1.1); } }'],
            
            // Lüks Temalar
            ['luks_boutique', 'Lüks Boutique', 'Lüks', 'Şık ve lüks görünüm, premium hissi', '#1A1A1A', '#C9B037', '#F8F8F8', '#1A1A1A', 'Montserrat, sans-serif', 30, 16, '.category-card { background: linear-gradient(135deg, #F8F8F8, #E8E8E8); border: 1px solid #C9B037; box-shadow: 0 8px 25px rgba(0,0,0,0.15); } .product-card { background: #FFFFFF; border-top: 3px solid #C9B037; }', '.category-card:hover { transform: translateY(-3px); box-shadow: 0 12px 35px rgba(0,0,0,0.25); transition: all 0.4s ease; }'],
            ['elmas_platin', 'Elmas Platin', 'Lüks', 'Platin renkleri, elmas parıltısı', '#2F4F4F', '#C0C0C0', '#F8F8FF', '#2F4F4F', 'Cinzel, serif', 32, 17, '.category-card { background: linear-gradient(135deg, #F8F8FF, #E6E6FA, #C0C0C0); border: 2px solid #C0C0C0; border-radius: 12px; position: relative; overflow: hidden; } .category-card::before { content: ""; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%; background: linear-gradient(45deg, transparent, rgba(255,255,255,0.3), transparent); animation: diamondShine 3s linear infinite; }', '.category-card:hover { animation: luxuryFloat 1s ease-in-out; } @keyframes luxuryFloat { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-5px); } } @keyframes diamondShine { 0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); } 100% { transform: translateX(100%) translateY(100%) rotate(45deg); } }'],
            ['altin_saray', 'Altın Saray', 'Lüks', 'Altın detaylar, saray atmosferi', '#8B4513', '#FFD700', '#FFFAF0', '#654321', 'Cinzel Decorative, serif', 36, 19, '.category-card { background: linear-gradient(135deg, #FFFAF0, #FFF8DC); border: 3px solid #FFD700; border-radius: 18px; box-shadow: 0 0 20px rgba(255,215,0,0.4); position: relative; } .category-card::after { content: "👑"; position: absolute; top: 10px; right: 15px; font-size: 26px; animation: crownGlow 2s ease-in-out infinite; }', '.category-card:hover { animation: royalElevate 0.8s ease-in-out; } @keyframes royalElevate { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.03); } } @keyframes crownGlow { 0%, 100% { filter: brightness(1); } 50% { filter: brightness(1.5) drop-shadow(0 0 8px #FFD700); } }'],
            
            // Doğa Temaları
            ['orman_yesili', 'Orman Yeşili', 'Doğa', 'Doğal yeşil tonlar, orman atmosferi', '#228B22', '#32CD32', '#F0FFF0', '#006400', 'Nunito, sans-serif', 30, 16, '.category-card { background: linear-gradient(135deg, #F0FFF0, #E0FFE0); border: 2px solid #228B22; border-radius: 20px; position: relative; } .category-card::before { content: "🌲"; position: absolute; top: 12px; left: 15px; font-size: 22px; animation: treeWave 4s ease-in-out infinite; }', '.category-card:hover { animation: natureBreath 1s ease-in-out; } @keyframes natureBreath { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.02); } } @keyframes treeWave { 0%, 100% { transform: rotate(0deg); } 25% { transform: rotate(2deg); } 75% { transform: rotate(-2deg); } }'],
            ['okyanus_mavisi', 'Okyanus Mavisi', 'Doğa', 'Okyanus derinlikleri, mavi tonlar', '#4682B4', '#00CED1', '#F0F8FF', '#191970', 'Raleway, sans-serif', 32, 17, '.category-card { background: linear-gradient(135deg, #F0F8FF, #E0F6FF, #B0E0E6); border: 2px solid #4682B4; border-radius: 25px; position: relative; overflow: hidden; } .category-card::after { content: "🌊"; position: absolute; bottom: 10px; right: 15px; font-size: 24px; animation: waveMotion 3s ease-in-out infinite; }', '.category-card:hover { animation: oceanFlow 1.2s ease-in-out; } @keyframes oceanFlow { 0%, 100% { transform: translateX(0px); } 33% { transform: translateX(3px); } 66% { transform: translateX(-3px); } } @keyframes waveMotion { 0%, 100% { transform: translateY(0px) rotate(0deg); } 50% { transform: translateY(-5px) rotate(10deg); } }'],
            ['gunes_batimi', 'Güneş Batımı', 'Doğa', 'Gün batımı renkleri, sıcak tonlar', '#FF6347', '#FFD700', '#FFF8DC', '#8B4513', 'Lora, serif', 34, 18, '.category-card { background: linear-gradient(135deg, #FFF8DC, #FFEFD5, #FFE4B5); border: 2px solid #FF6347; border-radius: 20px; box-shadow: 0 0 15px rgba(255,99,71,0.3); } .product-card { background: linear-gradient(135deg, #FFFAF0, #FFF8DC); }', '.category-card:hover { animation: sunsetGlow 1.5s ease-in-out infinite alternate; } @keyframes sunsetGlow { from { box-shadow: 0 0 15px rgba(255,99,71,0.3); } to { box-shadow: 0 0 25px rgba(255,215,0,0.6); } }'],
            
            // Teknoloji Temaları
            ['matrix_kod', 'Matrix Kod', 'Teknoloji', 'Matrix film teması, yeşil kodlar', '#000000', '#00FF00', '#0D1117', '#00FF00', 'Courier New, monospace', 28, 15, '.category-card { background: linear-gradient(135deg, #0D1117, #161B22); border: 2px solid #00FF00; border-radius: 8px; position: relative; overflow: hidden; } .category-card::before { content: "01010101"; position: absolute; top: 5px; right: 10px; font-size: 12px; color: #00FF00; opacity: 0.7; animation: codeRain 2s linear infinite; }', '.category-card:hover { animation: matrixGlitch 0.3s ease-in-out; } @keyframes matrixGlitch { 0%, 100% { transform: translateX(0px); } 20% { transform: translateX(-2px); } 40% { transform: translateX(2px); } 60% { transform: translateX(-1px); } 80% { transform: translateX(1px); } } @keyframes codeRain { 0% { opacity: 0.7; } 50% { opacity: 1; } 100% { opacity: 0.7; } }'],
            ['hologram', 'Hologram', 'Teknoloji', 'Holografik efektler, gelecek teması', '#4B0082', '#00FFFF', '#F8F8FF', '#4B0082', 'Exo 2, sans-serif', 30, 16, '.category-card { background: linear-gradient(135deg, #F8F8FF, #E6E6FA); border: 2px solid #4B0082; border-radius: 15px; position: relative; overflow: hidden; } .category-card::before { content: ""; position: absolute; top: 0; left: -100%; width: 100%; height: 100%; background: linear-gradient(90deg, transparent, rgba(0,255,255,0.2), transparent); animation: holoScan 3s linear infinite; }', '.category-card:hover { animation: holoFloat 1s ease-in-out; } @keyframes holoFloat { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-8px); } } @keyframes holoScan { 0% { left: -100%; } 100% { left: 100%; } }'],
            
            // Sanat Temaları
            ['sanat_galerisi', 'Sanat Galerisi', 'Sanat', 'Müze ve galeri atmosferi', '#2F2F2F', '#C0C0C0', '#FFFFFF', '#2F2F2F', 'Playfair Display, serif', 32, 17, '.category-card { background: linear-gradient(135deg, #FFFFFF, #F8F8F8); border: 3px solid #C0C0C0; border-radius: 5px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); } .product-card { background: #FFFFFF; border: 1px solid #E0E0E0; }', '.category-card:hover { transform: perspective(1000px) rotateX(5deg); transition: all 0.6s ease; }'],
            ['graffiti_sokak', 'Graffiti Sokak', 'Sanat', 'Sokak sanatı, renkli graffiti', '#FF4500', '#32CD32', '#2F2F2F', '#FFFFFF', 'Bangers, cursive', 36, 18, '.category-card { background: linear-gradient(135deg, #2F2F2F, #4A4A4A); border: 3px solid #FF4500; border-radius: 12px; position: relative; } .category-card::after { content: "🎨"; position: absolute; top: 10px; right: 15px; font-size: 24px; animation: paintSplash 2s ease-in-out infinite; }', '.category-card:hover { animation: streetVibe 0.4s ease-in-out; } @keyframes streetVibe { 0%, 100% { transform: rotate(0deg); } 25% { transform: rotate(1deg); } 75% { transform: rotate(-1deg); } } @keyframes paintSplash { 0%, 100% { transform: scale(1) rotate(0deg); } 50% { transform: scale(1.2) rotate(15deg); } }'],
            
            // Spor Temaları
            ['futbol_sahasi', 'Futbol Sahası', 'Spor', 'Futbol teması, yeşil saha', '#228B22', '#FFFFFF', '#90EE90', '#006400', 'Roboto Condensed, sans-serif', 32, 17, '.category-card { background: linear-gradient(135deg, #90EE90, #98FB98); border: 2px solid #228B22; border-radius: 10px; position: relative; } .category-card::before { content: "⚽"; position: absolute; top: 15px; right: 15px; font-size: 24px; animation: ballBounce 1.5s ease-in-out infinite; }', '.category-card:hover { animation: fieldRun 0.8s ease-in-out; } @keyframes fieldRun { 0%, 100% { transform: translateX(0px); } 25% { transform: translateX(5px); } 75% { transform: translateX(-5px); } } @keyframes ballBounce { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-10px); } }'],
            ['basketbol_kort', 'Basketbol Kortu', 'Spor', 'Basketbol teması, turuncu top', '#FF8C00', '#8B4513', '#F5DEB3', '#8B4513', 'Anton, sans-serif', 34, 18, '.category-card { background: linear-gradient(135deg, #F5DEB3, #DEB887); border: 3px solid #FF8C00; border-radius: 8px; } .product-card { background: #FAEBD7; border-left: 4px solid #FF8C00; }', '.category-card:hover { animation: dribble 0.6s ease-in-out; } @keyframes dribble { 0%, 100% { transform: translateY(0px); } 25% { transform: translateY(-5px); } 75% { transform: translateY(5px); } }'],
            
            // Müzik Temaları
            ['jazz_club', 'Jazz Club', 'Müzik', 'Jazz kulübü atmosferi, vintage', '#4B0082', '#FFD700', '#2F2F2F', '#FFD700', 'Bebas Neue, cursive', 36, 18, '.category-card { background: linear-gradient(135deg, #2F2F2F, #4A4A4A); border: 2px solid #FFD700; border-radius: 15px; box-shadow: 0 0 20px rgba(255,215,0,0.2); } .category-card::after { content: "🎷"; position: absolute; bottom: 10px; right: 15px; font-size: 26px; animation: jazzSway 3s ease-in-out infinite; }', '.category-card:hover { animation: jazzRhythm 1s ease-in-out; } @keyframes jazzRhythm { 0%, 100% { transform: rotate(0deg); } 25% { transform: rotate(2deg); } 75% { transform: rotate(-2deg); } } @keyframes jazzSway { 0%, 100% { transform: rotate(0deg); } 50% { transform: rotate(10deg); } }'],
            
            // Kafe Temaları - Eski Tarz
            ['vintage_coffee', 'Vintage Kahve Evi', 'Kafe', 'Nostaljik kahverengi tonlar, eski tarz', '#8B4513', '#D2691E', '#F5DEB3', '#654321', 'Dancing Script, cursive', 34, 17, '.category-card { background: linear-gradient(135deg, #F5DEB3, #DEB887); border: 3px solid #8B4513; border-radius: 15px; position: relative; } .category-card::before { content: "☕"; position: absolute; top: 10px; right: 15px; font-size: 24px; animation: steamRise 2s ease-in-out infinite; }', '.category-card:hover { animation: coffeeWarmth 1s ease-in-out; } @keyframes coffeeWarmth { 0%, 100% { box-shadow: 0 0 10px rgba(139,69,19,0.3); } 50% { box-shadow: 0 0 20px rgba(210,105,30,0.6); } } @keyframes steamRise { 0%, 100% { transform: translateY(0px) scale(1); } 50% { transform: translateY(-5px) scale(1.1); } }'],
            ['retro_bistro', 'Retro Bistro', 'Kafe', '70ler tarzı renkli desenler', '#FF6347', '#FFD700', '#FFF8DC', '#8B4513', 'Righteous, cursive', 32, 16, '.category-card { background: linear-gradient(45deg, #FFF8DC, #FFEFD5, #FFE4B5); border: 3px dashed #FF6347; border-radius: 20px; position: relative; } .category-card::after { content: "🎵"; position: absolute; bottom: 10px; left: 15px; font-size: 20px; animation: retroBeat 1.5s ease-in-out infinite; }', '.category-card:hover { animation: discoShine 0.8s ease-in-out; } @keyframes discoShine { 0%, 100% { background: linear-gradient(45deg, #FFF8DC, #FFEFD5, #FFE4B5); } 50% { background: linear-gradient(45deg, #FFE4B5, #FFD700, #FFF8DC); } } @keyframes retroBeat { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.2); } }'],
            ['classic_coffeehouse', 'Klasik Kahvehane', 'Kafe', 'Geleneksel Türk kahvesi teması', '#8B0000', '#D2691E', '#F5F5DC', '#654321', 'Amiri, serif', 36, 18, '.category-card { background: linear-gradient(135deg, #F5F5DC, #E6E6FA); border: 2px solid #8B0000; border-radius: 12px; position: relative; } .category-card::before { content: "🫖"; position: absolute; top: 12px; left: 15px; font-size: 22px; animation: teapotSteam 3s ease-in-out infinite; }', '.category-card:hover { animation: turkishDelight 1s ease-in-out; } @keyframes turkishDelight { 0%, 100% { transform: rotate(0deg); } 25% { transform: rotate(1deg); } 75% { transform: rotate(-1deg); } } @keyframes teapotSteam { 0%, 100% { transform: rotate(0deg); } 33% { transform: rotate(5deg); } 66% { transform: rotate(-5deg); } }'],
            ['old_vienna', 'Eski Viyana', 'Kafe', 'Avusturya kafe kültürü', '#8B4513', '#FFD700', '#FFFAF0', '#654321', 'Playfair Display, serif', 34, 17, '.category-card { background: linear-gradient(135deg, #FFFAF0, #FFF8DC); border: 3px solid #8B4513; border-radius: 18px; box-shadow: 0 0 15px rgba(139,69,19,0.3); } .category-card::after { content: "🏛️"; position: absolute; bottom: 10px; right: 15px; font-size: 24px; animation: viennaElegance 4s ease-in-out infinite; }', '.category-card:hover { animation: imperialGlow 1.2s ease-in-out; } @keyframes imperialGlow { 0%, 100% { box-shadow: 0 0 15px rgba(139,69,19,0.3); } 50% { box-shadow: 0 0 25px rgba(255,215,0,0.5); } } @keyframes viennaElegance { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.05); } }'],
            ['parisian_cafe', 'Paris Kafesi', 'Kafe', 'Fransız bistro atmosferi', '#4B0082', '#FF69B4', '#F8F8FF', '#2F2F2F', 'Courgette, cursive', 32, 16, '.category-card { background: linear-gradient(135deg, #F8F8FF, #E6E6FA); border: 2px solid #4B0082; border-radius: 25px; position: relative; } .category-card::before { content: "🗼"; position: absolute; top: 10px; right: 10px; font-size: 22px; animation: parisCharm 3s ease-in-out infinite; }', '.category-card:hover { animation: frenchElegance 1s ease-in-out; } @keyframes frenchElegance { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-5px); } } @keyframes parisCharm { 0%, 100% { transform: rotate(0deg); } 50% { transform: rotate(10deg); } }'],
            ['antique_roastery', 'Antika Kavurma', 'Kafe', 'Eski kahve kavurma makineleri', '#654321', '#8B4513', '#DEB887', '#3E2723', 'Fredericka the Great, cursive', 30, 15, '.category-card { background: radial-gradient(circle, #DEB887, #CD853F); border: 3px solid #654321; border-radius: 10px; position: relative; } .category-card::after { content: "⚙️"; position: absolute; bottom: 10px; left: 15px; font-size: 20px; animation: gearTurn 4s linear infinite; }', '.category-card:hover { animation: antiqueShake 0.5s ease-in-out; } @keyframes antiqueShake { 0%, 100% { transform: rotate(0deg); } 25% { transform: rotate(1deg); } 75% { transform: rotate(-1deg); } } @keyframes gearTurn { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }'],
            ['heritage_coffee', 'Miras Kahve', 'Kafe', 'Geleneksel aile işletmesi', '#8B4513', '#D2691E', '#F5DEB3', '#654321', 'Crimson Text, serif', 34, 17, '.category-card { background: linear-gradient(135deg, #F5DEB3, #DEB887); border: 2px solid #8B4513; border-radius: 15px; position: relative; } .category-card::before { content: "👨‍👩‍👧‍👦"; position: absolute; top: 10px; left: 10px; font-size: 18px; animation: familyWarmth 3s ease-in-out infinite; }', '.category-card:hover { animation: heritageGlow 1s ease-in-out; } @keyframes heritageGlow { 0%, 100% { box-shadow: 0 0 10px rgba(139,69,19,0.3); } 50% { box-shadow: 0 0 20px rgba(210,105,30,0.5); } } @keyframes familyWarmth { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.1); } }'],
            ['rustic_grind', 'Rustik Öğütme', 'Kafe', 'Ahşap ve doğal malzemeler', '#8B4513', '#228B22', '#F5DEB3', '#654321', 'Cabin, cursive', 32, 16, '.category-card { background: linear-gradient(135deg, #F5DEB3, #DEB887, #D2B48C); border: 3px solid #8B4513; border-radius: 12px; position: relative; } .category-card::after { content: "🌲"; position: absolute; top: 12px; right: 15px; font-size: 22px; animation: woodGrain 4s ease-in-out infinite; }', '.category-card:hover { animation: rusticCreak 0.6s ease-in-out; } @keyframes rusticCreak { 0%, 100% { transform: rotate(0deg); } 25% { transform: rotate(1deg); } 75% { transform: rotate(-1deg); } } @keyframes woodGrain { 0%, 100% { transform: rotate(0deg); } 50% { transform: rotate(5deg); } }'],
            ['vintage_espresso', 'Vintage Espresso', 'Kafe', 'İtalyan kafe geleneği', '#8B0000', '#FFD700', '#FFFAF0', '#654321', 'Satisfy, cursive', 36, 18, '.category-card { background: linear-gradient(135deg, #FFFAF0, #FFF8DC); border: 2px solid #8B0000; border-radius: 20px; position: relative; } .category-card::before { content: "🇮🇹"; position: absolute; top: 10px; left: 15px; font-size: 20px; animation: italianFlair 2s ease-in-out infinite; }', '.category-card:hover { animation: espressoRush 0.8s ease-in-out; } @keyframes espressoRush { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.03); } } @keyframes italianFlair { 0%, 100% { transform: rotate(0deg); } 50% { transform: rotate(15deg); } }'],
            ['old_world_brew', 'Eski Dünya', 'Kafe', 'Klasik kahve kültürü', '#654321', '#8B4513', '#F5DEB3', '#3E2723', 'Cinzel, serif', 32, 17, '.category-card { background: radial-gradient(ellipse, #F5DEB3, #DEB887); border: 3px double #654321; border-radius: 15px; position: relative; } .category-card::after { content: "🌍"; position: absolute; bottom: 10px; right: 15px; font-size: 24px; animation: worldSpin 8s linear infinite; }', '.category-card:hover { animation: oldWorldCharm 1s ease-in-out; } @keyframes oldWorldCharm { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-3px); } } @keyframes worldSpin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }'],
            
            // Kafe Temaları - Modern
            ['modern_minimalist_cafe', 'Modern Minimalist Kafe', 'Kafe', 'Sade ve şık tasarım', '#2C3E50', '#3498DB', '#FFFFFF', '#2C3E50', 'Roboto, sans-serif', 28, 16, '.category-card { background: linear-gradient(135deg, #FFFFFF, #F8F9FA); border: 2px solid #2C3E50; border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); } .category-card::before { content: "☕"; position: absolute; top: 15px; right: 15px; font-size: 22px; color: #3498DB; animation: modernPulse 2s ease-in-out infinite; }', '.category-card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.15); transition: all 0.3s ease; } @keyframes modernPulse { 0%, 100% { opacity: 0.7; } 50% { opacity: 1; } }'],
            ['urban_coffee', 'Şehirli Kahve', 'Kafe', 'Metropol yaşam tarzı', '#34495E', '#E74C3C', '#ECF0F1', '#2C3E50', 'Open Sans, sans-serif', 30, 17, '.category-card { background: linear-gradient(135deg, #ECF0F1, #D5DBDB); border: 2px solid #34495E; border-radius: 15px; position: relative; } .category-card::after { content: "🏙️"; position: absolute; bottom: 10px; left: 15px; font-size: 20px; animation: cityLife 3s ease-in-out infinite; }', '.category-card:hover { animation: urbanVibe 0.8s ease-in-out; } @keyframes urbanVibe { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.02); } } @keyframes cityLife { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-3px); } }'],
            ['industrial_brew', 'Endüstriyel Kahve', 'Kafe', 'Metal ve beton tasarım', '#5D6D7E', '#F39C12', '#F8F9FA', '#2C3E50', 'Oswald, sans-serif', 32, 18, '.category-card { background: linear-gradient(135deg, #F8F9FA, #E5E7E9); border: 3px solid #5D6D7E; border-radius: 8px; position: relative; } .category-card::before { content: "⚙️"; position: absolute; top: 12px; left: 15px; font-size: 24px; color: #F39C12; animation: industrialRotate 4s linear infinite; }', '.category-card:hover { animation: metalShine 1s ease-in-out; } @keyframes metalShine { 0%, 100% { background: linear-gradient(135deg, #F8F9FA, #E5E7E9); } 50% { background: linear-gradient(135deg, #E5E7E9, #D5DBDB); } } @keyframes industrialRotate { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }'],
            ['scandinavian_cafe', 'İskandinav Kafe', 'Kafe', 'Hygge konsepti', '#2E86AB', '#A23B72', '#F18F01', '#2C3E50', 'Lato, sans-serif', 28, 16, '.category-card { background: linear-gradient(135deg, #F18F01, #F4D03F); border: 2px solid #2E86AB; border-radius: 25px; position: relative; } .category-card::after { content: "🕯️"; position: absolute; top: 10px; right: 15px; font-size: 22px; animation: hyggeWarmth 3s ease-in-out infinite; }', '.category-card:hover { animation: cozyFeel 1s ease-in-out; } @keyframes cozyFeel { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.03); } } @keyframes hyggeWarmth { 0%, 100% { filter: brightness(1); } 50% { filter: brightness(1.2); } }'],
            ['artisan_coffee', 'Zanaatkar Kahve', 'Kafe', 'El yapımı özel kahveler', '#8B4513', '#D35400', '#FEF9E7', '#6C3483', 'Merriweather, serif', 34, 18, '.category-card { background: linear-gradient(135deg, #FEF9E7, #FCF3CF); border: 2px solid #8B4513; border-radius: 18px; position: relative; } .category-card::before { content: "👨‍🎨"; position: absolute; top: 12px; left: 15px; font-size: 20px; animation: artisanCraft 4s ease-in-out infinite; }', '.category-card:hover { animation: craftmanship 1s ease-in-out; } @keyframes craftmanship { 0%, 100% { box-shadow: 0 0 10px rgba(139,69,19,0.3); } 50% { box-shadow: 0 0 20px rgba(211,84,0,0.5); } } @keyframes artisanCraft { 0%, 100% { transform: rotate(0deg); } 25% { transform: rotate(5deg); } 75% { transform: rotate(-5deg); } }'],
            ['specialty_roast', 'Özel Kavurma', 'Kafe', 'Third wave coffee', '#922B21', '#F7DC6F', '#FDEAA7', '#6C3483', 'Playfair Display, serif', 32, 17, '.category-card { background: linear-gradient(135deg, #FDEAA7, #F7DC6F); border: 3px solid #922B21; border-radius: 15px; position: relative; } .category-card::after { content: "🔥"; position: absolute; bottom: 10px; right: 15px; font-size: 24px; animation: roastFlame 2s ease-in-out infinite; }', '.category-card:hover { animation: specialtyGlow 1s ease-in-out; } @keyframes specialtyGlow { 0%, 100% { box-shadow: 0 0 15px rgba(146,43,33,0.3); } 50% { box-shadow: 0 0 25px rgba(247,220,111,0.6); } } @keyframes roastFlame { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.2); } }'],
            ['craft_coffee', 'El Yapımı Kahve', 'Kafe', 'Butik kahve deneyimi', '#7D3C98', '#F4D03F', '#EBDEF0', '#4A235A', 'Dancing Script, cursive', 36, 19, '.category-card { background: linear-gradient(135deg, #EBDEF0, #D7BDE2); border: 2px solid #7D3C98; border-radius: 20px; position: relative; } .category-card::before { content: "✋"; position: absolute; top: 10px; right: 10px; font-size: 22px; animation: handCraft 3s ease-in-out infinite; }', '.category-card:hover { animation: boutiqueFeel 1s ease-in-out; } @keyframes boutiqueFeel { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-5px); } } @keyframes handCraft { 0%, 100% { transform: rotate(0deg); } 50% { transform: rotate(15deg); } }'],
            ['contemporary_blend', 'Çağdaş Karışım', 'Kafe', 'Modern kahve kültürü', '#1B4F72', '#F8C471', '#FEF9E7', '#2C3E50', 'Nunito, sans-serif', 30, 16, '.category-card { background: linear-gradient(135deg, #FEF9E7, #FCF3CF); border: 2px solid #1B4F72; border-radius: 22px; position: relative; } .category-card::after { content: "🌟"; position: absolute; top: 15px; left: 15px; font-size: 20px; animation: contemporaryShine 2.5s ease-in-out infinite; }', '.category-card:hover { animation: modernBlend 0.8s ease-in-out; } @keyframes modernBlend { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.02); } } @keyframes contemporaryShine { 0%, 100% { opacity: 0.7; } 50% { opacity: 1; } }'],
            ['hipster_grind', 'Hipster Öğütme', 'Kafe', 'Alternatif kafe kültürü', '#A93226', '#F7DC6F', '#2C3E50', '#FDEAA7', 'Indie Flower, cursive', 34, 18, '.category-card { background: linear-gradient(135deg, #2C3E50, #34495E); border: 3px solid #A93226; border-radius: 12px; position: relative; } .category-card::before { content: "🤓"; position: absolute; top: 10px; right: 15px; font-size: 22px; animation: hipsterNod 3s ease-in-out infinite; }', '.category-card:hover { animation: alternativeVibe 0.6s ease-in-out; } @keyframes alternativeVibe { 0%, 100% { transform: rotate(0deg); } 25% { transform: rotate(2deg); } 75% { transform: rotate(-2deg); } } @keyframes hipsterNod { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-3px); } }'],
            ['boutique_beans', 'Butik Çekirdekler', 'Kafe', 'Premium kahve seçkisi', '#6C3483', '#F4D03F', '#FADBD8', '#4A235A', 'Crimson Text, serif', 32, 17, '.category-card { background: linear-gradient(135deg, #FADBD8, #F2D7D5); border: 2px solid #6C3483; border-radius: 18px; position: relative; } .category-card::after { content: "💎"; position: absolute; bottom: 10px; left: 15px; font-size: 20px; animation: premiumSparkle 2s ease-in-out infinite; }', '.category-card:hover { animation: boutiqueElegance 1s ease-in-out; } @keyframes boutiqueElegance { 0%, 100% { box-shadow: 0 0 10px rgba(108,52,131,0.3); } 50% { box-shadow: 0 0 20px rgba(244,208,63,0.5); } } @keyframes premiumSparkle { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.3); } }'],
            ['rock_konseri', 'Rock Konseri', 'Müzik', 'Rock konseri enerjisi, koyu tonlar', '#8B0000', '#FF4500', '#1C1C1C', '#FF4500', 'Metal Mania, cursive', 38, 19, '.category-card { background: linear-gradient(135deg, #1C1C1C, #2F2F2F); border: 3px solid #8B0000; border-radius: 10px; box-shadow: 0 0 25px rgba(139,0,0,0.4); } .category-card::before { content: "🎸"; position: absolute; top: 12px; left: 15px; font-size: 28px; animation: rockOut 2s ease-in-out infinite; }', '.category-card:hover { animation: headBang 0.3s ease-in-out infinite; } @keyframes headBang { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-3px); } } @keyframes rockOut { 0%, 100% { transform: rotate(0deg); } 25% { transform: rotate(-10deg); } 75% { transform: rotate(10deg); } }']
        ];
        
        foreach ($temalar_data as $tema) {
            $stmt = $conn->prepare("INSERT INTO Temalar (tema_adi, tema_baslik, kategori, aciklama, ana_renk, ikincil_renk, arkaplan_rengi, metin_rengi, font_ailesi, baslik_font_boyutu, metin_font_boyutu, ozel_css, animasyon_css) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute($tema);
        }
    }
    
    // Tema değiştirme işlemi
    if (isset($_POST['tema_degistir']) && isset($_POST['secilen_tema'])) {
        $secilen_tema = $_POST['secilen_tema'];
        
        // Seçilen temayı veritabanından al
        $stmt = $conn->prepare("SELECT * FROM Temalar WHERE tema_adi = ?");
        $stmt->execute([$secilen_tema]);
        $tema = $stmt->fetch();
        
        if ($tema) {
            // Tema ayarlarını güncelle
            $tema_ayarlari = [
                'tema_adi' => $tema['tema_adi'],
                'ana_renk' => $tema['ana_renk'],
                'ikincil_renk' => $tema['ikincil_renk'],
                'arkaplan_rengi' => $tema['arkaplan_rengi'],
                'metin_rengi' => $tema['metin_rengi'],
                'font_ailesi' => $tema['font_ailesi'],
                'baslik_font_boyutu' => $tema['baslik_font_boyutu'],
                'metin_font_boyutu' => $tema['metin_font_boyutu']
            ];
            
            $basarili_guncelleme = 0;
            foreach ($tema_ayarlari as $ayar => $deger) {
                $stmt = $conn->prepare("UPDATE Ayarlar SET ayar_degeri = ?, updated_at = CURRENT_TIMESTAMP WHERE ayar_adi = ?");
                if ($stmt->execute([$deger, $ayar])) {
                    $basarili_guncelleme++;
                }
            }
            
            if ($basarili_guncelleme > 0) {
                $mesaj = "Tema başarıyla değiştirildi! (" . $tema['tema_baslik'] . ")";
            } else {
                $hata = "Tema değiştirilirken hata oluştu!";
            }
        } else {
            $hata = "Seçilen tema bulunamadı!";
        }
    }
    
    // Ayar güncelleme işlemi
    elseif ($_POST) {
        $guncellenecek_ayarlar = [
            'tema_adi', 'ana_renk', 'ikincil_renk', 'arkaplan_rengi', 'metin_rengi',
            'font_ailesi', 'baslik_font_boyutu', 'metin_font_boyutu',
            'logo_metni', 'alt_metin', 'bakim_modu'
        ];
        
        $basarili_guncelleme = 0;
        
        foreach ($guncellenecek_ayarlar as $ayar) {
            if (isset($_POST[$ayar])) {
                $deger = $_POST[$ayar];
                
                // Bakım modu checkbox kontrolü
                if ($ayar === 'bakim_modu') {
                    $deger = isset($_POST['bakim_modu']) ? '1' : '0';
                }
                
                $stmt = $conn->prepare("UPDATE Ayarlar SET ayar_degeri = ?, updated_at = CURRENT_TIMESTAMP WHERE ayar_adi = ?");
                if ($stmt->execute([$deger, $ayar])) {
                    $basarili_guncelleme++;
                }
            }
        }
        
        if ($basarili_guncelleme > 0) {
            $mesaj = "Ayarlar başarıyla güncellendi! ($basarili_guncelleme ayar güncellendi)";
        } else {
            $hata = "Ayarlar güncellenirken hata oluştu!";
        }
    }
    
    // Mevcut ayarları çek
    $ayarlar = [];
    $stmt = $conn->query("SELECT ayar_adi, ayar_degeri FROM Ayarlar");
    while ($row = $stmt->fetch()) {
        $ayarlar[$row['ayar_adi']] = $row['ayar_degeri'];
    }
    
    // Tüm temaları çek
    $temalar = [];
    $stmt = $conn->query("SELECT * FROM Temalar ORDER BY tema_baslik");
    while ($row = $stmt->fetch()) {
        $temalar[] = $row;
    }
    
} catch(PDOException $e) {
    $hata = "Veritabanı bağlantı hatası: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ayarlar - Tiryakideyim Yönetim Paneli</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Open+Sans:wght@300;400;600;700&family=Lato:wght@300;400;700&family=Montserrat:wght@300;400;500;600;700&family=Source+Sans+Pro:wght@300;400;600;700&family=Raleway:wght@300;400;500;600;700&family=Ubuntu:wght@300;400;500;700&family=Nunito:wght@300;400;600;700&family=Poppins:wght@300;400;500;600;700&family=Merriweather:wght@300;400;700&family=Playfair+Display:wght@400;500;600;700&family=Lora:wght@400;500;600;700&family=Dancing+Script:wght@400;500;600;700&family=Pacifico&display=swap" rel="stylesheet">
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
        
        .page-header {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .page-header h2 {
            color: #8B4513;
            margin: 0;
        }
        
        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 30px;
        }
        
        .settings-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .settings-section h3 {
            color: #8B4513;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #8B4513;
            font-weight: 600;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #D2691E;
        }
        
        .color-input {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .color-input input[type="color"] {
            width: 50px;
            height: 40px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .color-input input[type="text"] {
            flex: 1;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: auto;
        }
        
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: #D2691E;
            color: white;
        }
        
        .btn-primary:hover {
            background: #B8860B;
            transform: translateY(-2px);
        }
        
        .btn-success {
            background: #4CAF50;
            color: white;
        }
        
        .btn-success:hover {
            background: #45a049;
        }
        
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .preview-box {
            background: #f8f9fa;
            border: 2px dashed #ddd;
            border-radius: 10px;
            padding: 20px;
            margin-top: 15px;
            text-align: center;
        }
        
        .preview-logo {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .preview-subtitle {
            font-size: 16px;
            opacity: 0.8;
        }
        
        .font-preview {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
            margin-top: 10px;
            border: 1px solid #ddd;
        }
        
        .maintenance-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 5px;
            margin-top: 10px;
        }
        
        .save-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-top: 30px;
            text-align: center;
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
            
            .settings-grid {
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
                <li><a href="panel.php">📊 Dashboard</a></li>
                <li><a href="urunler.php">🍽️ Ürünler</a></li>
                <li><a href="kategoriler.php">📂 Kategoriler</a></li>
                <li><a href="adisyon.php">🧾 Adisyon</a></li>
                <li><a href="raporlar.php">📈 Raporlar</a></li>
                <li><a href="ayarlar.php" class="active">⚙️ Ayarlar</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <div class="page-header">
                <h2>⚙️ QR Menü Ayarları</h2>
                <p style="margin-top: 10px; color: #666;">Müşterilerinizin göreceği QR menünün görünümünü buradan özelleştirebilirsiniz.</p>
            </div>
            
            <?php if (!empty($mesaj)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($mesaj); ?></div>
            <?php endif; ?>
            
            <?php if (!empty($hata)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($hata); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="settings-grid">
                    <!-- Tema Seçimi -->
                    <div class="settings-section">
                        <h3>🎨 Profesyonel Temalar</h3>
                        <p style="color: #666; margin-bottom: 20px;">25+ farklı kategoriden profesyonel temalar. Animasyonlar ve görsel efektler dahil!</p>
                        
                        <!-- Kategori Filtreleri -->
                        <div class="kategori-filtreler" style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 20px; justify-content: center;">
                            <button type="button" class="kategori-btn active" onclick="filterThemes('all')" style="padding: 8px 16px; border: 2px solid #D2691E; background: #D2691E; color: white; border-radius: 20px; cursor: pointer; font-size: 12px; transition: all 0.3s ease;">Tümü</button>
                            <button type="button" class="kategori-btn" onclick="filterThemes('Modern')" style="padding: 8px 16px; border: 2px solid #D2691E; background: white; color: #D2691E; border-radius: 20px; cursor: pointer; font-size: 12px; transition: all 0.3s ease;">Modern</button>
                            <button type="button" class="kategori-btn" onclick="filterThemes('Klasik')" style="padding: 8px 16px; border: 2px solid #D2691E; background: white; color: #D2691E; border-radius: 20px; cursor: pointer; font-size: 12px; transition: all 0.3s ease;">Klasik</button>
                            <button type="button" class="kategori-btn" onclick="filterThemes('Mevsimsel')" style="padding: 8px 16px; border: 2px solid #D2691E; background: white; color: #D2691E; border-radius: 20px; cursor: pointer; font-size: 12px; transition: all 0.3s ease;">Mevsimsel</button>
                            <button type="button" class="kategori-btn" onclick="filterThemes('Özel Günler')" style="padding: 8px 16px; border: 2px solid #D2691E; background: white; color: #D2691E; border-radius: 20px; cursor: pointer; font-size: 12px; transition: all 0.3s ease;">Özel Günler</button>
                            <button type="button" class="kategori-btn" onclick="filterThemes('Lüks')" style="padding: 8px 16px; border: 2px solid #D2691E; background: white; color: #D2691E; border-radius: 20px; cursor: pointer; font-size: 12px; transition: all 0.3s ease;">Lüks</button>
                            <button type="button" class="kategori-btn" onclick="filterThemes('Doğa')" style="padding: 8px 16px; border: 2px solid #D2691E; background: white; color: #D2691E; border-radius: 20px; cursor: pointer; font-size: 12px; transition: all 0.3s ease;">Doğa</button>
                            <button type="button" class="kategori-btn" onclick="filterThemes('Teknoloji')" style="padding: 8px 16px; border: 2px solid #D2691E; background: white; color: #D2691E; border-radius: 20px; cursor: pointer; font-size: 12px; transition: all 0.3s ease;">Teknoloji</button>
                            <button type="button" class="kategori-btn" onclick="filterThemes('Sanat')" style="padding: 8px 16px; border: 2px solid #D2691E; background: white; color: #D2691E; border-radius: 20px; cursor: pointer; font-size: 12px; transition: all 0.3s ease;">Sanat</button>
                            <button type="button" class="kategori-btn" onclick="filterThemes('Spor')" style="padding: 8px 16px; border: 2px solid #D2691E; background: white; color: #D2691E; border-radius: 20px; cursor: pointer; font-size: 12px; transition: all 0.3s ease;">Spor</button>
                            <button type="button" class="kategori-btn" onclick="filterThemes('Müzik')" style="padding: 8px 16px; border: 2px solid #D2691E; background: white; color: #D2691E; border-radius: 20px; cursor: pointer; font-size: 12px; transition: all 0.3s ease;">Müzik</button>
                        </div>
                        
                        <div class="tema-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 20px;">
                            <?php foreach ($temalar as $tema): ?>
                                <div class="tema-card" data-kategori="<?php echo $tema['kategori']; ?>" style="border: 3px solid <?php echo ($ayarlar['tema_adi'] ?? 'modern_minimalist') === $tema['tema_adi'] ? '#4CAF50' : '#ddd'; ?>; border-radius: 15px; padding: 20px; text-align: center; cursor: pointer; transition: all 0.4s ease; position: relative; overflow: hidden; background: white; box-shadow: 0 4px 15px rgba(0,0,0,0.1);" onclick="selectTheme('<?php echo $tema['tema_adi']; ?>')">
                                    <div class="tema-kategori-badge" style="position: absolute; top: 10px; right: 10px; background: <?php echo $tema['ana_renk']; ?>; color: white; padding: 4px 8px; border-radius: 12px; font-size: 10px; font-weight: bold;"><?php echo $tema['kategori']; ?></div>
                                    
                                    <div class="tema-preview" style="background: <?php echo $tema['arkaplan_rengi']; ?>; color: <?php echo $tema['metin_rengi']; ?>; padding: 15px; border-radius: 10px; margin-bottom: 15px; font-family: <?php echo $tema['font_ailesi']; ?>; border: 2px solid <?php echo $tema['ana_renk']; ?>; position: relative; overflow: hidden;">
                                        <div style="color: <?php echo $tema['ana_renk']; ?>; font-size: 16px; font-weight: bold; margin-bottom: 8px;">🍽️ QR Menü</div>
                                        <div style="display: flex; flex-direction: column; gap: 4px;">
                                            <div style="background: <?php echo $tema['ikincil_renk']; ?>30; padding: 6px; border-radius: 6px; border-left: 3px solid <?php echo $tema['ikincil_renk']; ?>; font-size: 11px;">🍕 Pizza Kategorisi</div>
                                            <div style="background: <?php echo $tema['ikincil_renk']; ?>30; padding: 6px; border-radius: 6px; border-left: 3px solid <?php echo $tema['ikincil_renk']; ?>; font-size: 11px;">🍔 Burger Kategorisi</div>
                                            <div style="background: <?php echo $tema['ikincil_renk']; ?>30; padding: 6px; border-radius: 6px; border-left: 3px solid <?php echo $tema['ikincil_renk']; ?>; font-size: 11px;">🥗 Salata Kategorisi</div>
                                        </div>
                                    </div>
                                    
                                    <div class="tema-info">
                                        <h4 style="margin: 0 0 8px 0; color: #333; font-size: 16px;"><?php echo htmlspecialchars($tema['tema_baslik']); ?></h4>
                                        <p style="font-size: 12px; color: #666; margin: 0 0 10px 0; line-height: 1.4;"><?php echo htmlspecialchars($tema['aciklama']); ?></p>
                                        <div class="tema-renkler" style="display: flex; justify-content: center; gap: 6px; margin-bottom: 10px;">
                                            <span class="renk-ornek" style="width: 20px; height: 20px; border-radius: 50%; background: <?php echo $tema['ana_renk']; ?>; border: 2px solid white; box-shadow: 0 0 0 1px #ddd;"></span>
                                            <span class="renk-ornek" style="width: 20px; height: 20px; border-radius: 50%; background: <?php echo $tema['ikincil_renk']; ?>; border: 2px solid white; box-shadow: 0 0 0 1px #ddd;"></span>
                                            <span class="renk-ornek" style="width: 20px; height: 20px; border-radius: 50%; background: <?php echo $tema['arkaplan_rengi']; ?>; border: 2px solid white; box-shadow: 0 0 0 1px #ddd;"></span>
                                        </div>
                                    </div>
                                    
                                    <?php if (($ayarlar['tema_adi'] ?? 'modern_minimalist') === $tema['tema_adi']): ?>
                                        <div style="position: absolute; top: -5px; left: -5px; right: -5px; bottom: -5px; border: 3px solid #4CAF50; border-radius: 18px; pointer-events: none;"></div>
                                        <div style="background: #4CAF50; color: white; padding: 6px 12px; border-radius: 20px; font-size: 12px; margin-top: 10px; display: inline-block; font-weight: bold;">✓ AKTİF TEMA</div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div style="text-align: center; background: #f8f9fa; padding: 20px; border-radius: 10px; border: 2px dashed #ddd;">
                            <input type="hidden" name="secilen_tema" id="secilen_tema" value="<?php echo $ayarlar['tema_adi'] ?? 'modern_minimalist'; ?>">
                            <button type="submit" name="tema_degistir" class="btn btn-primary" style="padding: 12px 30px; font-size: 16px; margin-bottom: 10px;">🎨 Seçili Temayı Uygula</button><br>
                            <span style="color: #666; font-size: 14px;">Aktif tema: <strong id="secili_tema_adi" style="color: #4CAF50;"><?php 
                                $aktif_tema = array_filter($temalar, function($t) use ($ayarlar) { 
                                    return $t['tema_adi'] === ($ayarlar['tema_adi'] ?? 'modern_minimalist'); 
                                });
                                echo !empty($aktif_tema) ? reset($aktif_tema)['tema_baslik'] : 'Modern Minimalist';
                            ?></strong></span>
                        </div>
                    </div>
                    
                    <!-- Renk Ayarları -->
                    <div class="settings-section">
                        <h3>🎨 Renk Ayarları</h3>
                        
                        <div class="form-group">
                            <label for="ana_renk">Ana Tema Rengi</label>
                            <div class="color-input">
                                <input type="color" id="ana_renk_color" value="<?php echo $ayarlar['ana_renk'] ?? '#8B4513'; ?>" onchange="updateColorText('ana_renk')">
                                <input type="text" name="ana_renk" id="ana_renk" value="<?php echo $ayarlar['ana_renk'] ?? '#8B4513'; ?>" onchange="updateColorPicker('ana_renk')">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="ikincil_renk">İkincil Tema Rengi</label>
                            <div class="color-input">
                                <input type="color" id="ikincil_renk_color" value="<?php echo $ayarlar['ikincil_renk'] ?? '#D2691E'; ?>" onchange="updateColorText('ikincil_renk')">
                                <input type="text" name="ikincil_renk" id="ikincil_renk" value="<?php echo $ayarlar['ikincil_renk'] ?? '#D2691E'; ?>" onchange="updateColorPicker('ikincil_renk')">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="arkaplan_rengi">Arkaplan Rengi</label>
                            <div class="color-input">
                                <input type="color" id="arkaplan_rengi_color" value="<?php echo $ayarlar['arkaplan_rengi'] ?? '#f5f5f5'; ?>" onchange="updateColorText('arkaplan_rengi')">
                                <input type="text" name="arkaplan_rengi" id="arkaplan_rengi" value="<?php echo $ayarlar['arkaplan_rengi'] ?? '#f5f5f5'; ?>" onchange="updateColorPicker('arkaplan_rengi')">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="metin_rengi">Metin Rengi</label>
                            <div class="color-input">
                                <input type="color" id="metin_rengi_color" value="<?php echo $ayarlar['metin_rengi'] ?? '#333333'; ?>" onchange="updateColorText('metin_rengi')">
                                <input type="text" name="metin_rengi" id="metin_rengi" value="<?php echo $ayarlar['metin_rengi'] ?? '#333333'; ?>" onchange="updateColorPicker('metin_rengi')">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Font Ayarları -->
                    <div class="settings-section">
                        <h3>🔤 Font Ayarları</h3>
                        
                        <div class="form-group">
                            <label for="font_ailesi">Font Ailesi</label>
                            <select name="font_ailesi" id="font_ailesi" onchange="updateFontPreview()">
                                <?php foreach ($google_fonts as $value => $name): ?>
                                    <option value="<?php echo $value; ?>" <?php echo (($ayarlar['font_ailesi'] ?? 'Arial, sans-serif') === $value) ? 'selected' : ''; ?>>
                                        <?php echo $name; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="font-preview" id="fontPreview" style="font-family: <?php echo $ayarlar['font_ailesi'] ?? 'Arial, sans-serif'; ?>">
                                Bu font ile menü görünecek - Tiryakideyim Kahve
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="baslik_font_boyutu">Başlık Font Boyutu (px)</label>
                            <input type="number" name="baslik_font_boyutu" id="baslik_font_boyutu" 
                                   value="<?php echo $ayarlar['baslik_font_boyutu'] ?? '24'; ?>" min="12" max="48">
                        </div>
                        
                        <div class="form-group">
                            <label for="metin_font_boyutu">Normal Metin Font Boyutu (px)</label>
                            <input type="number" name="metin_font_boyutu" id="metin_font_boyutu" 
                                   value="<?php echo $ayarlar['metin_font_boyutu'] ?? '16'; ?>" min="10" max="24">
                        </div>
                    </div>
                    
                    <!-- Genel Ayarlar -->
                    <div class="settings-section">
                        <h3>🏪 Genel Ayarlar</h3>
                        
                        <div class="form-group">
                            <label for="logo_metni">Logo Metni</label>
                            <input type="text" name="logo_metni" id="logo_metni" 
                                   value="<?php echo htmlspecialchars($ayarlar['logo_metni'] ?? 'Tiryakideyim'); ?>" 
                                   onchange="updatePreview()">
                        </div>
                        
                        <div class="form-group">
                            <label for="alt_metin">Logo Alt Metni</label>
                            <input type="text" name="alt_metin" id="alt_metin" 
                                   value="<?php echo htmlspecialchars($ayarlar['alt_metin'] ?? 'Lezzetli kahveler ve tatlılar'); ?>" 
                                   onchange="updatePreview()">
                        </div>
                        
                        <div class="checkbox-group">
                            <input type="checkbox" name="bakim_modu" id="bakim_modu" 
                                   <?php echo (($ayarlar['bakim_modu'] ?? '0') === '1') ? 'checked' : ''; ?>>
                            <label for="bakim_modu">Bakım Modu (QR menü kapatılır)</label>
                        </div>
                        
                        <?php if (($ayarlar['bakim_modu'] ?? '0') === '1'): ?>
                            <div class="maintenance-warning">
                                ⚠️ <strong>Dikkat:</strong> Bakım modu açık! Müşteriler QR menüyü göremiyor.
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Önizleme -->
                    <div class="settings-section">
                        <h3>👁️ Önizleme</h3>
                        <p style="color: #666; margin-bottom: 15px;">QR menünüzün nasıl görüneceğinin önizlemesi:</p>
                        
                        <div class="preview-box" id="previewBox" 
                             style="background: <?php echo $ayarlar['arkaplan_rengi'] ?? '#f5f5f5'; ?>; color: <?php echo $ayarlar['metin_rengi'] ?? '#333333'; ?>; font-family: <?php echo $ayarlar['font_ailesi'] ?? 'Arial, sans-serif'; ?>">
                            <div class="preview-logo" id="previewLogo" 
                                 style="color: <?php echo $ayarlar['ana_renk'] ?? '#8B4513'; ?>; font-size: <?php echo $ayarlar['baslik_font_boyutu'] ?? '24'; ?>px;">
                                <?php echo htmlspecialchars($ayarlar['logo_metni'] ?? 'Tiryakideyim'); ?>
                            </div>
                            <div class="preview-subtitle" id="previewSubtitle" 
                                 style="font-size: <?php echo $ayarlar['metin_font_boyutu'] ?? '16'; ?>px;">
                                <?php echo htmlspecialchars($ayarlar['alt_metin'] ?? 'Lezzetli kahveler ve tatlılar'); ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="save-section">
                    <button type="submit" class="btn btn-success">
                        💾 Ayarları Kaydet
                    </button>
                    <p style="margin-top: 15px; color: #666; font-size: 14px;">
                        Değişiklikler kaydedildikten sonra QR menüde görünecektir.
                    </p>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Tema seçim fonksiyonları
        function selectTheme(themeId) {
            // Tüm tema kartlarının seçimini kaldır
            document.querySelectorAll('.tema-card').forEach(card => {
                card.style.border = '3px solid #ddd';
                // Aktif tema border'ını kaldır
                const activeBorder = card.querySelector('div[style*="position: absolute"][style*="border: 3px solid #4CAF50"]');
                if (activeBorder) {
                    activeBorder.remove();
                }
                // Aktif tema badge'ini kaldır
                const activeBadge = card.querySelector('div[style*="AKTİF TEMA"]');
                if (activeBadge) {
                    activeBadge.remove();
                }
            });
            
            // Seçilen tema kartını vurgula
            event.currentTarget.style.border = '3px solid #4CAF50';
            
            // Aktif tema border'ını ekle
            const activeBorder = document.createElement('div');
            activeBorder.style.cssText = 'position: absolute; top: -5px; left: -5px; right: -5px; bottom: -5px; border: 3px solid #4CAF50; border-radius: 18px; pointer-events: none;';
            event.currentTarget.appendChild(activeBorder);
            
            // Aktif tema badge'ini ekle
            const activeBadge = document.createElement('div');
            activeBadge.style.cssText = 'background: #4CAF50; color: white; padding: 6px 12px; border-radius: 20px; font-size: 12px; margin-top: 10px; display: inline-block; font-weight: bold;';
            activeBadge.textContent = '✓ AKTİF TEMA';
            event.currentTarget.appendChild(activeBadge);
            
            // Hidden input'u güncelle
            document.getElementById('secilen_tema').value = themeId;
            
            // Tema adını güncelle
            const themeTitle = event.currentTarget.querySelector('h4').textContent;
            document.getElementById('secili_tema_adi').textContent = themeTitle;
        }
        
        // Kategori filtreleme fonksiyonu
        function filterThemes(kategori) {
            const cards = document.querySelectorAll('.tema-card');
            const buttons = document.querySelectorAll('.kategori-btn');
            
            // Buton stillerini güncelle
            buttons.forEach(btn => {
                btn.classList.remove('active');
                btn.style.background = 'white';
                btn.style.color = '#D2691E';
            });
            
            // Aktif butonu işaretle
            const activeBtn = document.querySelector(`[onclick="filterThemes('${kategori}')"]`);
            if (activeBtn) {
                activeBtn.classList.add('active');
                activeBtn.style.background = '#D2691E';
                activeBtn.style.color = 'white';
            }
            
            // Tema kartlarını filtrele
            cards.forEach(card => {
                const cardKategori = card.getAttribute('data-kategori');
                if (kategori === 'all' || cardKategori === kategori) {
                    card.style.display = 'block';
                    card.style.animation = 'fadeIn 0.5s ease-in-out';
                } else {
                    card.style.display = 'none';
                }
            });
        }
        
        function updateColorText(colorName) {
            const colorPicker = document.getElementById(colorName + '_color');
            const textInput = document.getElementById(colorName);
            textInput.value = colorPicker.value;
            updatePreview();
        }
        
        function updateColorPicker(colorName) {
            const textInput = document.getElementById(colorName);
            const colorPicker = document.getElementById(colorName + '_color');
            colorPicker.value = textInput.value;
            updatePreview();
        }
        
        function updateFontPreview() {
            const fontSelect = document.getElementById('font_ailesi');
            const preview = document.getElementById('fontPreview');
            preview.style.fontFamily = fontSelect.value;
            updatePreview();
        }
        
        function updatePreview() {
            const previewBox = document.getElementById('previewBox');
            const previewLogo = document.getElementById('previewLogo');
            const previewSubtitle = document.getElementById('previewSubtitle');
            
            // Renkleri güncelle
            previewBox.style.background = document.getElementById('arkaplan_rengi').value;
            previewBox.style.color = document.getElementById('metin_rengi').value;
            previewBox.style.fontFamily = document.getElementById('font_ailesi').value;
            
            previewLogo.style.color = document.getElementById('ana_renk').value;
            previewLogo.style.fontSize = document.getElementById('baslik_font_boyutu').value + 'px';
            previewLogo.textContent = document.getElementById('logo_metni').value;
            
            previewSubtitle.style.fontSize = document.getElementById('metin_font_boyutu').value + 'px';
            previewSubtitle.textContent = document.getElementById('alt_metin').value;
        }
        
        // CSS animasyonları ve hover efektleri
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(20px); }
                to { opacity: 1; transform: translateY(0); }
            }
            
            @keyframes pulse {
                0% { transform: scale(1); }
                50% { transform: scale(1.05); }
                100% { transform: scale(1); }
            }
            
            .tema-card {
                transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
            }
            
            .tema-card:hover {
                transform: translateY(-8px) scale(1.02) !important;
                box-shadow: 0 12px 30px rgba(0,0,0,0.2) !important;
                border-color: #4CAF50 !important;
            }
            
            .kategori-btn {
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            }
            
            .kategori-btn:hover {
                transform: translateY(-2px) scale(1.05) !important;
                box-shadow: 0 6px 20px rgba(210, 105, 30, 0.4) !important;
            }
            
            .tema-preview {
                transition: all 0.3s ease !important;
            }
            
            .tema-card:hover .tema-preview {
                transform: scale(1.05) !important;
            }
            
            .tema-kategori-badge {
                animation: pulse 2s infinite !important;
            }
            
            .renk-ornek {
                transition: all 0.3s ease !important;
            }
            
            .tema-card:hover .renk-ornek {
                transform: scale(1.2) !important;
            }
        `;
        document.head.appendChild(style);
        
        // Sayfa yüklendiğinde çalışacak fonksiyonlar
        document.addEventListener('DOMContentLoaded', function() {
            updatePreview();
            
            // Tema kartlarına animasyon ekle
            document.querySelectorAll('.tema-card').forEach((card, index) => {
                card.style.animation = `fadeIn 0.6s ease-out ${index * 0.1}s both`;
            });
        });
    </script>
</body>
</html>