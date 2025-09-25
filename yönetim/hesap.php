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
    $toplam_acik_hesap = $conn->query("SELECT COUNT(*) FROM acik_hesaplar WHERE durum = 'aktif'")->fetchColumn();
    $toplam_veresiye = $conn->query("SELECT COUNT(*) FROM veresiye_musteriler WHERE durum = 'aktif'")->fetchColumn();
    $toplam_acik_borc = $conn->query("SELECT SUM(mevcut_borc) FROM acik_hesaplar WHERE durum = 'aktif'")->fetchColumn() ?: 0;
    $toplam_veresiye_borc = $conn->query("SELECT SUM(mevcut_borc) FROM veresiye_musteriler WHERE durum = 'aktif'")->fetchColumn() ?: 0;
    
    // Açık hesapları çek
    $acik_hesaplar = $conn->query("SELECT * FROM acik_hesaplar WHERE durum = 'aktif' ORDER BY hesap_adi ASC")->fetchAll();
    
    // Veresiye müşterilerini çek
    $veresiye_musteriler = $conn->query("SELECT * FROM veresiye_musteriler WHERE durum = 'aktif' ORDER BY ad_soyad ASC")->fetchAll();
    
} catch(PDOException $e) {
    $hata = "Veritabanı bağlantı hatası: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tiryakideyim - Hesap Yönetimi</title>
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
            padding: 20px 0;
        }
        
        .sidebar ul {
            list-style: none;
        }
        
        .sidebar li {
            margin: 5px 0;
        }
        
        .sidebar a {
            display: block;
            padding: 12px 25px;
            color: #666;
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }
        
        .sidebar a:hover, .sidebar a.active {
            background: #f8f9fa;
            color: #8B4513;
            border-left-color: #8B4513;
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
        
        .stat-number {
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #8B4513;
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
        
        .section {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .section-header {
            background: linear-gradient(135deg, #8B4513, #D2691E);
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .section-content {
            padding: 20px;
        }
        
        .btn {
            background: linear-gradient(135deg, #8B4513, #D2691E);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(139,69,19,0.3);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #28a745, #20c997);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #dc3545, #e74c3c);
        }
        
        .btn-warning {
            background: linear-gradient(135deg, #ffc107, #ffb300);
            color: #333;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        .table th, .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #8B4513;
        }
        
        .table tr:hover {
            background: #f8f9fa;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 20px;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
            position: relative;
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: #000;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .form-group textarea {
            height: 80px;
            resize: vertical;
        }
        
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
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
        
        .text-success {
            color: #28a745;
        }
        
        .text-danger {
            color: #dc3545;
        }
        
        .text-warning {
            color: #ffc107;
        }
        
        .text-success {
            color: #28a745;
        }
        
        .text-danger {
            color: #dc3545;
        }
        
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        
        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
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
            <span>💳</span>
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
                <li><a href="hesap.php" class="active">💳 Hesap Yönetimi</a></li>
                <li><a href="ayarlar.php">⚙️ Ayarlar</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <div class="welcome-section">
                <h2>Hesap Yönetimi</h2>
                <p>Açık hesaplar ve veresiye müşterilerinizi buradan yönetebilirsiniz. Borç takibi ve ödeme işlemlerini kolayca gerçekleştirebilirsiniz.</p>
            </div>
            
            <?php if (isset($hata)): ?>
                <div style="background: #ffebee; color: #c62828; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                    <?php echo htmlspecialchars($hata); ?>
                </div>
            <?php else: ?>
                <!-- İstatistikler -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">🏢</div>
                        <div class="stat-number"><?php echo $toplam_acik_hesap; ?></div>
                        <div class="stat-label">Aktif Açık Hesap</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">👤</div>
                        <div class="stat-number"><?php echo $toplam_veresiye; ?></div>
                        <div class="stat-label">Aktif Veresiye Müşteri</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">💰</div>
                        <div class="stat-number"><?php echo number_format($toplam_acik_borc, 2); ?> ₺</div>
                        <div class="stat-label">Toplam Açık Hesap Borcu</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">💳</div>
                        <div class="stat-number"><?php echo number_format($toplam_veresiye_borc, 2); ?> ₺</div>
                        <div class="stat-label">Toplam Veresiye Borcu</div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Açık Hesaplar -->
            <div class="section">
                <div class="section-header">
                    <h2>🏢 Açık Hesaplar</h2>
                    <button class="btn btn-success" onclick="openModal('acikHesapModal')">+ Yeni Açık Hesap</button>
                </div>
                <div class="section-content">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Hesap Adı</th>
                                <th>Firma</th>
                                <th>Yetkili</th>
                                <th>Telefon</th>
                                <th>Kredi Limiti</th>
                                <th>Mevcut Borç</th>
                                <th>Kullanılabilir</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($acik_hesaplar as $hesap): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($hesap['hesap_adi']); ?></strong></td>
                                <td><?php echo htmlspecialchars($hesap['firma_adi']); ?></td>
                                <td><?php echo htmlspecialchars($hesap['yetkili_kisi']); ?></td>
                                <td><?php echo htmlspecialchars($hesap['telefon']); ?></td>
                                <td class="text-success"><?php echo number_format($hesap['kredi_limiti'], 2); ?> ₺</td>
                                <td class="<?php echo $hesap['mevcut_borc'] > 0 ? 'text-danger' : 'text-success'; ?>">
                                    <?php echo number_format($hesap['mevcut_borc'], 2); ?> ₺
                                </td>
                                <td class="text-success"><?php echo number_format($hesap['kredi_limiti'] - $hesap['mevcut_borc'], 2); ?> ₺</td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-sm btn-warning" onclick="editAcikHesap(<?php echo $hesap['id']; ?>)">Düzenle</button>
                                        <button class="btn btn-sm btn-success" onclick="odemeYap('acik_hesap', <?php echo $hesap['id']; ?>)">Ödeme</button>
                                        <button class="btn btn-sm" onclick="hesapDetay('acik_hesap', <?php echo $hesap['id']; ?>)">Detay</button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Veresiye Müşteriler -->
            <div class="section">
                <div class="section-header">
                    <h2>👤 Veresiye Müşteriler</h2>
                    <button class="btn btn-success" onclick="openModal('veresiyeModal')">+ Yeni Veresiye Müşteri</button>
                </div>
                <div class="section-content">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Ad Soyad</th>
                                <th>Telefon</th>
                                <th>Mevcut Borç</th>
                                <th>Durum</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($veresiye_musteriler as $musteri): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($musteri['ad_soyad']); ?></strong></td>
                                <td><?php echo htmlspecialchars($musteri['telefon']); ?></td>
                                <td class="<?php echo $musteri['mevcut_borc'] > 0 ? 'text-danger' : 'text-success'; ?>">
                                    <?php echo number_format($musteri['mevcut_borc'], 2); ?> ₺
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $musteri['durum'] == 'aktif' ? 'success' : 'danger'; ?>">
                                        <?php echo ucfirst($musteri['durum']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-sm btn-warning" onclick="editVeresiye(<?php echo $musteri['id']; ?>)">Düzenle</button>
                                        <button class="btn btn-sm btn-success" onclick="odemeYap('veresiye', <?php echo $musteri['id']; ?>)">Ödeme</button>
                                        <button class="btn btn-sm" onclick="hesapDetay('veresiye', <?php echo $musteri['id']; ?>)">Detay</button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Açık Hesap Modal -->
    <div id="acikHesapModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('acikHesapModal')">&times;</span>
            <h2>Yeni Açık Hesap</h2>
            <form id="acikHesapForm">
                <div class="form-group">
                    <label>Hesap Adı *</label>
                    <input type="text" name="hesap_adi" required>
                </div>
                <div class="form-group">
                    <label>Firma Adı</label>
                    <input type="text" name="firma_adi">
                </div>
                <div class="form-group">
                    <label>Yetkili Kişi</label>
                    <input type="text" name="yetkili_kisi">
                </div>
                <div class="form-group">
                    <label>Telefon</label>
                    <input type="text" name="telefon">
                </div>
                <div class="form-group">
                    <label>E-mail</label>
                    <input type="email" name="email">
                </div>
                <div class="form-group">
                    <label>Adres</label>
                    <textarea name="adres"></textarea>
                </div>
                <div class="form-group">
                    <label>Kredi Limiti (₺)</label>
                    <input type="number" name="kredi_limiti" step="0.01" value="0.00">
                </div>
                <div class="form-group">
                    <label>Notlar</label>
                    <textarea name="notlar"></textarea>
                </div>
                <button type="submit" class="btn">Kaydet</button>
            </form>
        </div>
    </div>
    
    <!-- Veresiye Modal -->
    <div id="veresiyeModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('veresiyeModal')">&times;</span>
            <h2>Yeni Veresiye Müşteri</h2>
            <form id="veresiyeForm">
                <div class="form-group">
                    <label>Ad Soyad *</label>
                    <input type="text" name="ad_soyad" required>
                </div>
                <div class="form-group">
                    <label>Telefon</label>
                    <input type="text" name="telefon">
                </div>
                <div class="form-group">
                    <label>Adres</label>
                    <textarea name="adres"></textarea>
                </div>
                <div class="form-group">
                    <label>Notlar</label>
                    <textarea name="notlar"></textarea>
                </div>
                <button type="submit" class="btn">Kaydet</button>
            </form>
        </div>
    </div>
    
    <!-- Ödeme Modal -->
    <div id="odemeModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('odemeModal')">&times;</span>
            <h2>Ödeme Yap</h2>
            <form id="odemeForm">
                <input type="hidden" name="hesap_tipi" id="odeme_hesap_tipi">
                <input type="hidden" name="hesap_id" id="odeme_hesap_id">
                <div class="form-group">
                    <label>Ödeme Tutarı (₺) *</label>
                    <input type="number" name="odeme_tutari" step="0.01" required>
                </div>
                <div class="form-group">
                    <label>Ödeme Yöntemi</label>
                    <select name="odeme_yontemi">
                        <option value="nakit">Nakit</option>
                        <option value="kart">Kart</option>
                        <option value="havale">Havale</option>
                        <option value="diger">Diğer</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Açıklama</label>
                    <textarea name="aciklama"></textarea>
                </div>
                <button type="submit" class="btn">Ödeme Yap</button>
            </form>
        </div>
    </div>
    
    <script>
        // Modal işlemleri
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        // Açık hesap formu
        document.getElementById('acikHesapForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('hesap_islemler.php?action=add_acik_hesap', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Açık hesap başarıyla eklendi!');
                    location.reload();
                } else {
                    alert('Hata: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Hata:', error);
                alert('Bir hata oluştu!');
            });
        });
        
        // Veresiye formu
        document.getElementById('veresiyeForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('hesap_islemler.php?action=add_veresiye', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Veresiye müşteri başarıyla eklendi!');
                    location.reload();
                } else {
                    alert('Hata: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Hata:', error);
                alert('Bir hata oluştu!');
            });
        });
        
        // Ödeme formu
        document.getElementById('odemeForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('hesap_islemler.php?action=odeme_yap', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Ödeme başarıyla kaydedildi!');
                    location.reload();
                } else {
                    alert('Hata: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Hata:', error);
                alert('Bir hata oluştu!');
            });
        });
        
        // Ödeme modalını aç
        function odemeYap(hesapTipi, hesapId) {
            document.getElementById('odeme_hesap_tipi').value = hesapTipi;
            document.getElementById('odeme_hesap_id').value = hesapId;
            openModal('odemeModal');
        }
        
        // Hesap detayı
        function hesapDetay(hesapTipi, hesapId) {
            window.open(`hesap_detay.php?tip=${hesapTipi}&id=${hesapId}`, '_blank');
        }
        
        // Düzenleme fonksiyonları
        function editAcikHesap(id) {
            // TODO: Düzenleme modalı açılacak
            alert('Düzenleme özelliği yakında eklenecek!');
        }
        
        function editVeresiye(id) {
            // TODO: Düzenleme modalı açılacak
            alert('Düzenleme özelliği yakında eklenecek!');
        }
        
        // Modal dışına tıklandığında kapatma
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>