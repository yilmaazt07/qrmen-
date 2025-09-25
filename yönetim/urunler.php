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

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Kategorileri çek
    $kategoriler = $conn->query("SELECT * FROM Kategoriler ORDER BY sira ASC, kategori_adi ASC")->fetchAll();
    
    // Ürün silme işlemi
    if (isset($_GET['sil']) && is_numeric($_GET['sil'])) {
        // Önce ürünün görselini al
        $stmt = $conn->prepare("SELECT gorsel_url FROM Urunler WHERE id = ?");
        $stmt->execute([$_GET['sil']]);
        $silinecek_urun = $stmt->fetch();
        
        $stmt = $conn->prepare("DELETE FROM Urunler WHERE id = ?");
        if ($stmt->execute([$_GET['sil']])) {
            // Ürün silindiyse görselini de sil
            if ($silinecek_urun && !empty($silinecek_urun['gorsel_url']) && file_exists('../' . $silinecek_urun['gorsel_url'])) {
                unlink('../' . $silinecek_urun['gorsel_url']);
            }
            $mesaj = "Ürün başarıyla silindi!";
        } else {
            $hata = "Ürün silinirken hata oluştu!";
        }
    }
    
    // Ürün ekleme/güncelleme işlemi
    if ($_POST) {
        $urun_id = $_POST['urun_id'] ?? '';
        $kategori_id = $_POST['kategori_id'] ?? '';
        $urun_adi = $_POST['urun_adi'] ?? '';
        $aciklama = $_POST['aciklama'] ?? '';
        $gorsel_url = $_POST['gorsel_url'] ?? '';
        $fiyat = $_POST['fiyat'] ?? '';
        $alerji_uyari = $_POST['alerji_uyari'] ?? '';
        $kafe_spesiyali = isset($_POST['kafe_spesiyali']) ? 1 : 0;
        $gorunurluk = isset($_POST['gorunurluk']) ? 1 : 0;
        $sira = $_POST['sira'] ?? 0;
        
        // Dosya yükleme işlemi
        if (isset($_FILES['gorsel']) && $_FILES['gorsel']['error'] == 0) {
            $dosya = $_FILES['gorsel'];
            $dosya_adi = $dosya['name'];
            $dosya_tmp = $dosya['tmp_name'];
            $dosya_boyut = $dosya['size'];
            $dosya_uzanti = strtolower(pathinfo($dosya_adi, PATHINFO_EXTENSION));
            
            // İzin verilen dosya türleri
            $izinli_uzantilar = ['jpg', 'jpeg', 'png', 'webp'];
            
            if (in_array($dosya_uzanti, $izinli_uzantilar)) {
                if ($dosya_boyut <= 5000000) { // 5MB limit
                    $yeni_dosya_adi = uniqid() . '.' . $dosya_uzanti;
                    $hedef_yol = '../uploads/' . $yeni_dosya_adi;
                    
                    if (move_uploaded_file($dosya_tmp, $hedef_yol)) {
                        $gorsel_url = 'uploads/' . $yeni_dosya_adi;
                        
                        // Eski görseli sil (güncelleme durumunda)
                        if (!empty($urun_id)) {
                            $stmt = $conn->prepare("SELECT gorsel_url FROM Urunler WHERE id = ?");
                            $stmt->execute([$urun_id]);
                            $eski_urun = $stmt->fetch();
                            if ($eski_urun && !empty($eski_urun['gorsel_url']) && file_exists('../' . $eski_urun['gorsel_url'])) {
                                unlink('../' . $eski_urun['gorsel_url']);
                            }
                        }
                    } else {
                        $hata = "Dosya yüklenirken hata oluştu!";
                    }
                } else {
                    $hata = "Dosya boyutu 5MB'dan büyük olamaz!";
                }
            } else {
                $hata = "Sadece JPG, JPEG, PNG ve WebP dosyaları yüklenebilir!";
            }
        }
        
        if (!empty($kategori_id) && !empty($urun_adi) && !empty($fiyat) && empty($hata)) {
            if (empty($urun_id)) {
                // Yeni ürün ekleme
                $stmt = $conn->prepare("INSERT INTO Urunler (kategori_id, urun_adi, aciklama, gorsel_url, fiyat, alerji_uyari, kafe_spesiyali, gorunurluk, sira) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                if ($stmt->execute([$kategori_id, $urun_adi, $aciklama, $gorsel_url, $fiyat, $alerji_uyari, $kafe_spesiyali, $gorunurluk, $sira])) {
                    $mesaj = "Ürün başarıyla eklendi!";
                } else {
                    $hata = "Ürün eklenirken hata oluştu!";
                }
            } else {
                // Ürün güncelleme
                $stmt = $conn->prepare("UPDATE Urunler SET kategori_id=?, urun_adi=?, aciklama=?, gorsel_url=?, fiyat=?, alerji_uyari=?, kafe_spesiyali=?, gorunurluk=?, sira=? WHERE id=?");
                if ($stmt->execute([$kategori_id, $urun_adi, $aciklama, $gorsel_url, $fiyat, $alerji_uyari, $kafe_spesiyali, $gorunurluk, $sira, $urun_id])) {
                    $mesaj = "Ürün başarıyla güncellendi!";
                } else {
                    $hata = "Ürün güncellenirken hata oluştu!";
                }
            }
        } else if (empty($hata)) {
            $hata = "Lütfen zorunlu alanları doldurun! (Kategori, Ürün Adı, Fiyat)";
        }
    }
    
    // Düzenleme için ürün bilgilerini çek
    $duzenle_urun = null;
    if (isset($_GET['duzenle']) && is_numeric($_GET['duzenle'])) {
        $stmt = $conn->prepare("SELECT * FROM Urunler WHERE id = ?");
        $stmt->execute([$_GET['duzenle']]);
        $duzenle_urun = $stmt->fetch();
    }
    
    // Ürünleri çek (kategorilerle birlikte)
    $urunler = $conn->query("
        SELECT u.*, k.kategori_adi 
        FROM Urunler u 
        LEFT JOIN Kategoriler k ON u.kategori_id = k.id 
        ORDER BY u.sira ASC, u.urun_adi ASC
    ")->fetchAll();
    
} catch(PDOException $e) {
    $hata = "Veritabanı bağlantı hatası: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ürünler - Tiryakideyim Yönetim Paneli</title>
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
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .page-header h2 {
            color: #8B4513;
            margin: 0;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: #D2691E;
            color: white;
        }
        
        .btn-primary:hover {
            background: #B8860B;
        }
        
        .btn-success {
            background: #4CAF50;
            color: white;
        }
        
        .btn-success:hover {
            background: #45a049;
        }
        
        .btn-danger {
            background: #f44336;
            color: white;
        }
        
        .btn-danger:hover {
            background: #da190b;
        }
        
        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
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
        
        .form-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
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
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: auto;
        }
        
        .table-section {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table th,
        .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #8B4513;
        }
        
        .table tr:hover {
            background: #f8f9fa;
        }
        
        .badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .badge-success {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }
        
        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }
        
        .price {
            font-weight: bold;
            color: #D2691E;
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
            
            .page-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .table {
                font-size: 12px;
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
                <li><a href="urunler.php" class="active">🍽️ Ürünler</a></li>
                <li><a href="kategoriler.php">📂 Kategoriler</a></li>
                <li><a href="adisyon.php">🧾 Adisyon</a></li>
                <li><a href="raporlar.php">📈 Raporlar</a></li>
                <li><a href="ayarlar.php">⚙️ Ayarlar</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <div class="page-header">
                <h2>🍽️ Ürün Yönetimi</h2>
                <a href="#" onclick="toggleForm()" class="btn btn-primary">+ Yeni Ürün Ekle</a>
            </div>
            
            <?php if (!empty($mesaj)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($mesaj); ?></div>
            <?php endif; ?>
            
            <?php if (!empty($hata)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($hata); ?></div>
            <?php endif; ?>
            
            <!-- Ürün Ekleme/Düzenleme Formu -->
            <div class="form-section" id="urunForm" style="<?php echo ($duzenle_urun || !empty($_POST)) ? 'display: block;' : 'display: none;'; ?>">
                <h3><?php echo $duzenle_urun ? 'Ürün Düzenle' : 'Yeni Ürün Ekle'; ?></h3>
                <form method="POST" action="" enctype="multipart/form-data">
                    <?php if ($duzenle_urun): ?>
                        <input type="hidden" name="urun_id" value="<?php echo $duzenle_urun['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="kategori_id">Kategori *</label>
                            <select name="kategori_id" id="kategori_id" required>
                                <option value="">Kategori Seçin</option>
                                <?php foreach ($kategoriler as $kategori): ?>
                                    <option value="<?php echo $kategori['id']; ?>" 
                                        <?php echo ($duzenle_urun && $duzenle_urun['kategori_id'] == $kategori['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($kategori['kategori_adi']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="urun_adi">Ürün Adı *</label>
                            <input type="text" name="urun_adi" id="urun_adi" 
                                   value="<?php echo $duzenle_urun ? htmlspecialchars($duzenle_urun['urun_adi']) : ''; ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="fiyat">Fiyat (TL) *</label>
                            <input type="number" step="0.01" name="fiyat" id="fiyat" 
                                   value="<?php echo $duzenle_urun ? $duzenle_urun['fiyat'] : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="sira">Sıra</label>
                            <input type="number" name="sira" id="sira" 
                                   value="<?php echo $duzenle_urun ? $duzenle_urun['sira'] : '0'; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="aciklama">Açıklama</label>
                        <textarea name="aciklama" id="aciklama" rows="3"><?php echo $duzenle_urun ? htmlspecialchars($duzenle_urun['aciklama']) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="gorsel">Ürün Görseli</label>
                            <input type="file" name="gorsel" id="gorsel" accept=".jpg,.jpeg,.png,.webp">
                            <small style="color: #666; font-size: 12px;">Desteklenen formatlar: JPG, JPEG, PNG, WebP (Maksimum 5MB)</small>
                            <?php if ($duzenle_urun && !empty($duzenle_urun['gorsel_url'])): ?>
                                <div style="margin-top: 10px;">
                                    <img src="../<?php echo htmlspecialchars($duzenle_urun['gorsel_url']); ?>" 
                                         alt="Mevcut görsel" style="max-width: 100px; max-height: 100px; border-radius: 5px;">
                                    <br><small style="color: #666;">Mevcut görsel (Yeni dosya seçerseniz değiştirilecek)</small>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="alerji_uyari">Alerji Uyarısı</label>
                            <input type="text" name="alerji_uyari" id="alerji_uyari" 
                                   value="<?php echo $duzenle_urun ? htmlspecialchars($duzenle_urun['alerji_uyari']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="checkbox-group">
                            <input type="checkbox" name="kafe_spesiyali" id="kafe_spesiyali" 
                                   <?php echo ($duzenle_urun && $duzenle_urun['kafe_spesiyali']) ? 'checked' : ''; ?>>
                            <label for="kafe_spesiyali">Kafe Spesiyali</label>
                        </div>
                        
                        <div class="checkbox-group">
                            <input type="checkbox" name="gorunurluk" id="gorunurluk" 
                                   <?php echo (!$duzenle_urun || $duzenle_urun['gorunurluk']) ? 'checked' : ''; ?>>
                            <label for="gorunurluk">Menüde Görünsün</label>
                        </div>
                    </div>
                    
                    <div style="margin-top: 20px;">
                        <button type="submit" class="btn btn-success">
                            <?php echo $duzenle_urun ? 'Güncelle' : 'Ekle'; ?>
                        </button>
                        <a href="urunler.php" class="btn btn-danger">İptal</a>
                    </div>
                </form>
            </div>
            
            <!-- Ürün Listesi -->
            <div class="table-section">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Görsel</th>
                            <th>Sıra</th>
                            <th>Ürün Adı</th>
                            <th>Kategori</th>
                            <th>Fiyat</th>
                            <th>Durum</th>
                            <th>Spesiyal</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($urunler)): ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 40px; color: #666;">
                                    Henüz ürün eklenmemiş. İlk ürününüzü eklemek için "Yeni Ürün Ekle" butonunu kullanın.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($urunler as $urun): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($urun['gorsel_url'])): ?>
                                            <img src="../<?php echo htmlspecialchars($urun['gorsel_url']); ?>" 
                                                 alt="<?php echo htmlspecialchars($urun['urun_adi']); ?>" 
                                                 style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                        <?php else: ?>
                                            <div style="width: 50px; height: 50px; background: #f0f0f0; border-radius: 5px; display: flex; align-items: center; justify-content: center; color: #999; font-size: 12px;">Görsel Yok</div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $urun['sira']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($urun['urun_adi']); ?></strong>
                                        <?php if (!empty($urun['aciklama'])): ?>
                                            <br><small style="color: #666;"><?php echo htmlspecialchars(substr($urun['aciklama'], 0, 50)) . (strlen($urun['aciklama']) > 50 ? '...' : ''); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($urun['kategori_adi'] ?? 'Kategori Yok'); ?></td>
                                    <td class="price"><?php echo number_format($urun['fiyat'], 2); ?> TL</td>
                                    <td>
                                        <?php if ($urun['gorunurluk']): ?>
                                            <span class="badge badge-success">Görünür</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Gizli</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($urun['kafe_spesiyali']): ?>
                                            <span class="badge badge-warning">⭐ Spesiyal</span>
                                        <?php else: ?>
                                            <span class="badge badge-success">Normal</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="?duzenle=<?php echo $urun['id']; ?>" class="btn btn-primary btn-sm">Düzenle</a>
                                        <a href="?sil=<?php echo $urun['id']; ?>" class="btn btn-danger btn-sm" 
                                           onclick="return confirm('Bu ürünü silmek istediğinizden emin misiniz?')">Sil</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script>
        function toggleForm() {
            const form = document.getElementById('urunForm');
            if (form.style.display === 'none' || form.style.display === '') {
                form.style.display = 'block';
                form.scrollIntoView({ behavior: 'smooth' });
            } else {
                form.style.display = 'none';
            }
        }
    </script>
</body>
</html>