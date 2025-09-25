<?php
session_start();

// Veritabanı bağlantı bilgileri
$servername = "localhost";
$username = "ymc15dmasycomtr_qrmenutk";
$password = "1b58b79a!A";
$dbname = "ymc15dmasycomtr_qrmenu";

// Hatalı giriş sayacı
if (!isset($_SESSION['hatali_giris'])) {
    $_SESSION['hatali_giris'] = 0;
}

$hata_mesaji = "";

// Form gönderildiğinde
if ($_POST) {
    $kullanici_adi = $_POST['kullanici_adi'] ?? '';
    $sifre = $_POST['sifre'] ?? '';
    $beni_hatirla = isset($_POST['beni_hatirla']);
    
    if (!empty($kullanici_adi) && !empty($sifre)) {
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $stmt = $conn->prepare("SELECT * FROM Admins WHERE kullanici_adi = ? AND sifre = ?");
            $stmt->execute([$kullanici_adi, $sifre]);
            $admin = $stmt->fetch();
            
            if ($admin) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_adi'] = $admin['ad_soyad'];
                $_SESSION['hatali_giris'] = 0;
                
                if ($beni_hatirla) {
                    setcookie('admin_kullanici', $kullanici_adi, time() + (30 * 24 * 60 * 60), '/');
                }
                
                header('Location: panel.php');
                exit;
            } else {
                $_SESSION['hatali_giris']++;
                if ($_SESSION['hatali_giris'] >= 3) {
                    $hata_mesaji = "Hatalı Giriş! 3 kez yanlış giriş yaptınız.";
                } else {
                    $hata_mesaji = "Kullanıcı adı veya şifre hatalı!";
                }
            }
        } catch(PDOException $e) {
            $hata_mesaji = "Bağlantı hatası: " . $e->getMessage();
        }
    } else {
        $hata_mesaji = "Lütfen tüm alanları doldurun!";
    }
}

// Cookie'den kullanıcı adını al
$kayitli_kullanici = $_COOKIE['admin_kullanici'] ?? '';
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
            background: linear-gradient(135deg, #8B4513 0%, #D2691E 50%, #CD853F 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 400px;
            backdrop-filter: blur(10px);
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo h1 {
            color: #8B4513;
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .logo p {
            color: #D2691E;
            font-size: 16px;
            font-weight: 500;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #8B4513;
            font-weight: 600;
            font-size: 14px;
        }
        
        .form-group input[type="text"],
        .form-group input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #DDD;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #FAFAFA;
        }
        
        .form-group input[type="text"]:focus,
        .form-group input[type="password"]:focus {
            outline: none;
            border-color: #D2691E;
            background: #FFF;
            box-shadow: 0 0 0 3px rgba(210, 105, 30, 0.1);
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .checkbox-group input[type="checkbox"] {
            margin-right: 10px;
            transform: scale(1.2);
            accent-color: #D2691E;
        }
        
        .checkbox-group label {
            color: #666;
            font-size: 14px;
            cursor: pointer;
        }
        
        .login-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #8B4513, #D2691E);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .login-btn:hover {
            background: linear-gradient(135deg, #A0522D, #F4A460);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(139, 69, 19, 0.4);
        }
        
        .error-message {
            background: #FFE6E6;
            color: #D32F2F;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #D32F2F;
            font-size: 14px;
            font-weight: 500;
        }
        
        .coffee-icon {
            font-size: 40px;
            margin-bottom: 10px;
        }
        
        @media (max-width: 480px) {
            .login-container {
                margin: 20px;
                padding: 30px 25px;
            }
            
            .logo h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <div class="coffee-icon">☕</div>
            <h1>Tiryakideyim</h1>
            <p>Yönetim Paneli</p>
        </div>
        
        <?php if (!empty($hata_mesaji)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($hata_mesaji); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="kullanici_adi">Kullanıcı Adı</label>
                <input type="text" id="kullanici_adi" name="kullanici_adi" 
                       value="<?php echo htmlspecialchars($kayitli_kullanici); ?>" 
                       required autocomplete="username">
            </div>
            
            <div class="form-group">
                <label for="sifre">Şifre</label>
                <input type="password" id="sifre" name="sifre" required autocomplete="current-password">
            </div>
            
            <div class="checkbox-group">
                <input type="checkbox" id="beni_hatirla" name="beni_hatirla" 
                       <?php echo !empty($kayitli_kullanici) ? 'checked' : ''; ?>>
                <label for="beni_hatirla">Beni Hatırla</label>
            </div>
            
            <button type="submit" class="login-btn">Giriş Yap</button>
        </form>
    </div>
</body>
</html>