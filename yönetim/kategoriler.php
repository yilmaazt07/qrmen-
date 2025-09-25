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
    
    // Kategori silme işlemi
    if (isset($_GET['sil']) && is_numeric($_GET['sil'])) {
        // Önce alt kategorileri kontrol et
        $stmt = $conn->prepare("SELECT COUNT(*) FROM Kategoriler WHERE parent_id = ?");
        $stmt->execute([$_GET['sil']]);
        $alt_kategori_sayisi = $stmt->fetchColumn();
        
        // Kategoriye ait ürünleri kontrol et
        $stmt = $conn->prepare("SELECT COUNT(*) FROM Urunler WHERE kategori_id = ?");
        $stmt->execute([$_GET['sil']]);
        $urun_sayisi = $stmt->fetchColumn();
        
        if ($alt_kategori_sayisi > 0) {
            $hata = "Bu kategorinin alt kategorileri var. Önce alt kategorileri silin!";
        } elseif ($urun_sayisi > 0) {
            $hata = "Bu kategoriye ait ürünler var. Önce ürünleri silin veya başka kategoriye taşıyın!";
        } else {
            // Kategori görselini al
            $stmt = $conn->prepare("SELECT gorsel_url FROM Kategoriler WHERE id = ?");
            $stmt->execute([$_GET['sil']]);
            $kategori_gorsel = $stmt->fetchColumn();
            
            $stmt = $conn->prepare("DELETE FROM Kategoriler WHERE id = ?");
            if ($stmt->execute([$_GET['sil']])) {
                // Görsel dosyasını sil
                if (!empty($kategori_gorsel)) {
                    $gorsel_yolu = '../' . $kategori_gorsel;
                    if (file_exists($gorsel_yolu)) {
                        unlink($gorsel_yolu);
                    }
                }
                $mesaj = "Kategori başarıyla silindi!";
            } else {
                $hata = "Kategori silinirken hata oluştu!";
            }
        }
    }
    
    // Veritabanı tablosunu güncelle (görsel alanı ekle)
    try {
        $conn->exec("ALTER TABLE Kategoriler ADD COLUMN gorsel_url VARCHAR(255) AFTER kategori_adi");
    } catch(PDOException $e) {
        // Alan zaten varsa hata verme
    }
    
    // Kategori ekleme/güncelleme işlemi
    if ($_POST) {
        $kategori_id = $_POST['kategori_id'] ?? '';
        $parent_id = $_POST['parent_id'] ?? null;
        $kategori_adi = $_POST['kategori_adi'] ?? '';
        $gorunurluk = isset($_POST['gorunurluk']) ? 1 : 0;
        $sira = $_POST['sira'] ?? 0;
        $gorsel_url = '';
        
        // Parent_id boşsa null yap
        if (empty($parent_id)) {
            $parent_id = null;
        }
        
        // Dosya yükleme işlemi
        if (isset($_FILES['kategori_gorsel']) && $_FILES['kategori_gorsel']['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
            $max_size = 5 * 1024 * 1024; // 5MB
            
            if (in_array($_FILES['kategori_gorsel']['type'], $allowed_types)) {
                if ($_FILES['kategori_gorsel']['size'] <= $max_size) {
                    $upload_dir = '../uploads/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    $file_extension = pathinfo($_FILES['kategori_gorsel']['name'], PATHINFO_EXTENSION);
                    $new_filename = 'kategori_' . time() . '_' . rand(1000, 9999) . '.' . $file_extension;
                    $upload_path = $upload_dir . $new_filename;
                    
                    if (move_uploaded_file($_FILES['kategori_gorsel']['tmp_name'], $upload_path)) {
                        $gorsel_url = 'uploads/' . $new_filename;
                        
                        // Eski görseli sil (güncelleme durumunda)
                        if (!empty($kategori_id) && $duzenle_kategori && !empty($duzenle_kategori['gorsel_url'])) {
                            $eski_gorsel = '../' . $duzenle_kategori['gorsel_url'];
                            if (file_exists($eski_gorsel)) {
                                unlink($eski_gorsel);
                            }
                        }
                    } else {
                        $hata = "Görsel yüklenirken hata oluştu!";
                    }
                } else {
                    $hata = "Görsel boyutu 5MB'dan küçük olmalıdır!";
                }
            } else {
                $hata = "Sadece JPG, PNG ve WebP formatları desteklenir!";
            }
        } elseif (!empty($kategori_id) && $duzenle_kategori) {
            // Güncelleme durumunda mevcut görseli koru
            $gorsel_url = $duzenle_kategori['gorsel_url'];
        }
        
        if (!empty($kategori_adi) && empty($hata)) {
            if (empty($kategori_id)) {
                // Yeni kategori ekleme
                $stmt = $conn->prepare("INSERT INTO Kategoriler (parent_id, kategori_adi, gorsel_url, gorunurluk, sira) VALUES (?, ?, ?, ?, ?)");
                if ($stmt->execute([$parent_id, $kategori_adi, $gorsel_url, $gorunurluk, $sira])) {
                    $mesaj = "Kategori başarıyla eklendi!";
                } else {
                    $hata = "Kategori eklenirken hata oluştu!";
                }
            } else {
                // Kategori güncelleme - kendi kendisinin alt kategorisi olamaz
                if ($parent_id == $kategori_id) {
                    $hata = "Bir kategori kendi alt kategorisi olamaz!";
                } else {
                    $stmt = $conn->prepare("UPDATE Kategoriler SET parent_id=?, kategori_adi=?, gorsel_url=?, gorunurluk=?, sira=? WHERE id=?");
                    if ($stmt->execute([$parent_id, $kategori_adi, $gorsel_url, $gorunurluk, $sira, $kategori_id])) {
                        $mesaj = "Kategori başarıyla güncellendi!";
                    } else {
                        $hata = "Kategori güncellenirken hata oluştu!";
                    }
                }
            }
        } else {
            $hata = "Lütfen kategori adını girin!";
        }
    }
    
    // Düzenleme için kategori bilgilerini çek
    $duzenle_kategori = null;
    if (isset($_GET['duzenle']) && is_numeric($_GET['duzenle'])) {
        $stmt = $conn->prepare("SELECT * FROM Kategoriler WHERE id = ?");
        $stmt->execute([$_GET['duzenle']]);
        $duzenle_kategori = $stmt->fetch();
    }
    
    // Tüm kategorileri çek (hiyerarşik yapı için)
    $kategoriler = $conn->query("
        SELECT k1.*, k2.kategori_adi as parent_adi,
               (SELECT COUNT(*) FROM Kategoriler WHERE parent_id = k1.id) as alt_kategori_sayisi,
               (SELECT COUNT(*) FROM Urunler WHERE kategori_id = k1.id) as urun_sayisi
        FROM Kategoriler k1 
        LEFT JOIN Kategoriler k2 ON k1.parent_id = k2.id 
        ORDER BY k1.parent_id ASC, k1.sira ASC, k1.kategori_adi ASC
    ")->fetchAll();
    
    // Ana kategorileri çek (parent_id = NULL)
    $ana_kategoriler = $conn->query("SELECT * FROM Kategoriler WHERE parent_id IS NULL ORDER BY sira ASC, kategori_adi ASC")->fetchAll();
    
    // Tüm kategorileri hiyerarşik olarak çek (üst kategori seçimi için)
    $tum_kategoriler = $conn->query("SELECT * FROM Kategoriler ORDER BY parent_id ASC, sira ASC, kategori_adi ASC")->fetchAll();
    
    // Hiyerarşik kategori listesi oluştur
    function buildHierarchy($categories, $parent_id = null, $level = 0) {
        $result = [];
        foreach ($categories as $category) {
            if ($category['parent_id'] == $parent_id) {
                $category['level'] = $level;
                $result[] = $category;
                $children = buildHierarchy($categories, $category['id'], $level + 1);
                $result = array_merge($result, $children);
            }
        }
        return $result;
    }
    
    $hiyerarsik_kategoriler = buildHierarchy($tum_kategoriler);
    
} catch(PDOException $e) {
    $hata = "Veritabanı bağlantı hatası: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategoriler - Tiryakideyim Yönetim Paneli</title>
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
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .form-group input:focus,
        .form-group select:focus {
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
        
        .badge-info {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }
        
        .category-hierarchy {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .sub-category {
            padding-left: 20px;
            position: relative;
        }
        
        .sub-category::before {
            content: "└─";
            position: absolute;
            left: 0;
            color: #999;
        }
        
        .stats {
            font-size: 12px;
            color: #666;
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
                <li><a href="urunler.php">🍽️ Ürünler</a></li>
                <li><a href="kategoriler.php" class="active">📂 Kategoriler</a></li>
                <li><a href="adisyon.php">🧾 Adisyon</a></li>
                <li><a href="raporlar.php">📈 Raporlar</a></li>
                <li><a href="ayarlar.php">⚙️ Ayarlar</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <div class="page-header">
                <h2>📂 Kategori Yönetimi</h2>
                <a href="#" onclick="toggleForm()" class="btn btn-primary">+ Yeni Kategori Ekle</a>
            </div>
            
            <?php if (!empty($mesaj)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($mesaj); ?></div>
            <?php endif; ?>
            
            <?php if (!empty($hata)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($hata); ?></div>
            <?php endif; ?>
            
            <!-- Kategori Ekleme/Düzenleme Formu -->
            <div class="form-section" id="kategoriForm" style="<?php echo ($duzenle_kategori || !empty($_POST)) ? 'display: block;' : 'display: none;'; ?>">
                <h3><?php echo $duzenle_kategori ? 'Kategori Düzenle' : 'Yeni Kategori Ekle'; ?></h3>
                <form method="POST" action="" enctype="multipart/form-data">
                    <?php if ($duzenle_kategori): ?>
                        <input type="hidden" name="kategori_id" value="<?php echo $duzenle_kategori['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="kategori_adi">Kategori Adı *</label>
                            <input type="text" name="kategori_adi" id="kategori_adi" 
                                   value="<?php echo $duzenle_kategori ? htmlspecialchars($duzenle_kategori['kategori_adi']) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="kategori_gorsel">Kategori Görseli</label>
                            <input type="file" name="kategori_gorsel" id="kategori_gorsel" 
                                   accept=".jpg,.jpeg,.png,.webp">
                            <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">
                                📷 Desteklenen formatlar: JPG, PNG, WebP (Maksimum 5MB)
                            </small>
                            <?php if ($duzenle_kategori && !empty($duzenle_kategori['gorsel_url'])): ?>
                                <div style="margin-top: 10px;">
                                    <img src="../<?php echo htmlspecialchars($duzenle_kategori['gorsel_url']); ?>" 
                                         alt="Mevcut görsel" style="max-width: 100px; max-height: 100px; border-radius: 8px; border: 2px solid #ddd;">
                                    <p style="font-size: 12px; color: #666; margin-top: 5px;">Mevcut görsel</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        
                        <div class="form-group">
                            <label for="parent_id">Üst Kategori</label>
                            <select name="parent_id" id="parent_id">
                                <option value="">Ana Kategori (Üst kategori yok)</option>
                                <?php foreach ($hiyerarsik_kategoriler as $kategori): ?>
                                    <?php if (!$duzenle_kategori || $kategori['id'] != $duzenle_kategori['id']): ?>
                                        <?php 
                                        // Alt kategorinin kendi üst kategorisi olamayacağını kontrol et
                                        $can_select = true;
                                        if ($duzenle_kategori) {
                                            // Düzenlenen kategorinin alt kategorilerini kontrol et
                                            $temp_categories = $tum_kategoriler;
                                            $check_children = function($parent_id, $target_id) use (&$check_children, $temp_categories) {
                                                foreach ($temp_categories as $cat) {
                                                    if ($cat['parent_id'] == $parent_id) {
                                                        if ($cat['id'] == $target_id) return true;
                                                        if ($check_children($cat['id'], $target_id)) return true;
                                                    }
                                                }
                                                return false;
                                            };
                                            if ($check_children($duzenle_kategori['id'], $kategori['id'])) {
                                                $can_select = false;
                                            }
                                        }
                                        ?>
                                        <?php if ($can_select): ?>
                                            <option value="<?php echo $kategori['id']; ?>" 
                                                <?php echo ($duzenle_kategori && $duzenle_kategori['parent_id'] == $kategori['id']) ? 'selected' : ''; ?>>
                                                <?php echo str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $kategori['level']) . '└─ ' . htmlspecialchars($kategori['kategori_adi']); ?>
                                                <?php if ($kategori['level'] == 0): ?>
                                                    <span style="color: #999;"> (Ana Kategori)</span>
                                                <?php else: ?>
                                                    <span style="color: #999;"> (Alt Kategori)</span>
                                                <?php endif; ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                            <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">
                                💡 İpucu: Alt kategoriler için üst kategori seçebilirsiniz. Hiyerarşik yapı desteklenir.
                            </small>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="sira">Sıra</label>
                            <input type="number" name="sira" id="sira" 
                                   value="<?php echo $duzenle_kategori ? $duzenle_kategori['sira'] : '0'; ?>">
                        </div>
                        
                        <div class="checkbox-group">
                            <input type="checkbox" name="gorunurluk" id="gorunurluk" 
                                   <?php echo (!$duzenle_kategori || $duzenle_kategori['gorunurluk']) ? 'checked' : ''; ?>>
                            <label for="gorunurluk">Menüde Görünsün</label>
                        </div>
                    </div>
                    
                    <div style="margin-top: 20px;">
                        <button type="submit" class="btn btn-success">
                            <?php echo $duzenle_kategori ? 'Güncelle' : 'Ekle'; ?>
                        </button>
                        <a href="kategoriler.php" class="btn btn-danger">İptal</a>
                    </div>
                </form>
            </div>
            
            <!-- Kategori Listesi -->
            <div class="table-section">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Sıra</th>
                            <th>Görsel</th>
                            <th>Kategori Adı</th>
                            <th>Üst Kategori</th>
                            <th>Durum</th>
                            <th>İstatistikler</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($kategoriler)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 40px; color: #666;">
                                    Henüz kategori eklenmemiş. İlk kategorinizi eklemek için "Yeni Kategori Ekle" butonunu kullanın.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($kategoriler as $kategori): ?>
                                <tr>
                                    <td><?php echo $kategori['sira']; ?></td>
                                    <td>
                                        <?php if (!empty($kategori['gorsel_url'])): ?>
                                            <img src="../<?php echo htmlspecialchars($kategori['gorsel_url']); ?>" 
                                                 alt="<?php echo htmlspecialchars($kategori['kategori_adi']); ?>" 
                                                 style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px; border: 2px solid #ddd;">
                                        <?php else: ?>
                                            <div style="width: 50px; height: 50px; background: #f0f0f0; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 20px; color: #999; border: 2px solid #ddd;">
                                                📂
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="<?php echo $kategori['parent_id'] ? 'sub-category' : ''; ?>">
                                            <strong><?php echo htmlspecialchars($kategori['kategori_adi']); ?></strong>
                                            <?php if ($kategori['parent_id']): ?>
                                                <span class="badge badge-info">Alt Kategori</span>
                                            <?php else: ?>
                                                <span class="badge badge-warning">Ana Kategori</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($kategori['parent_adi']): ?>
                                            <?php echo htmlspecialchars($kategori['parent_adi']); ?>
                                        <?php else: ?>
                                            <span style="color: #999;">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($kategori['gorunurluk']): ?>
                                            <span class="badge badge-success">Görünür</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Gizli</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="stats">
                                            <div>🍽️ <?php echo $kategori['urun_sayisi']; ?> ürün</div>
                                            <div>📂 <?php echo $kategori['alt_kategori_sayisi']; ?> alt kategori</div>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="?duzenle=<?php echo $kategori['id']; ?>" class="btn btn-primary btn-sm">Düzenle</a>
                                        <a href="?sil=<?php echo $kategori['id']; ?>" class="btn btn-danger btn-sm" 
                                           onclick="return confirm('Bu kategoriyi silmek istediğinizden emin misiniz?\n\nNot: Alt kategoriler veya ürünler varsa silinemez!')">Sil</a>
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
            const form = document.getElementById('kategoriForm');
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