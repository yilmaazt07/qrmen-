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
    
    // Kategorileri çek
    $kategoriler = $conn->query("SELECT * FROM Kategoriler WHERE gorunurluk = 1 ORDER BY sira ASC, kategori_adi ASC")->fetchAll();
    
    // Ürünleri çek
    $urunler = $conn->query("SELECT u.*, k.kategori_adi FROM Urunler u JOIN Kategoriler k ON u.kategori_id = k.id WHERE u.gorunurluk = 1 ORDER BY u.kategori_id, u.sira ASC, u.urun_adi ASC")->fetchAll();
    
} catch(PDOException $e) {
    $hata = "Veritabanı bağlantı hatası: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adisyon Sistemi - Tiryakideyim</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #8B4513 0%, #D2691E 50%, #F4A460 100%);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
            height: 100vh;
            overflow: hidden;
            touch-action: manipulation;
        }
        
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        /* Hesap Modal Stilleri */
        .account-modal, .veresiye-modal, .acik-hesap-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            z-index: 10000;
            backdrop-filter: blur(5px);
        }
        
        .account-container, .veresiye-container, .acik-hesap-container {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 20px;
            padding: 30px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        
        .account-header, .veresiye-header, .acik-hesap-header {
            text-align: center;
            margin-bottom: 25px;
        }
        
        .account-header h3, .veresiye-header h3, .acik-hesap-header h3 {
            margin: 0 0 10px 0;
            color: #2c3e50;
            font-size: 24px;
            font-weight: 800;
        }
        
        .account-header p, .veresiye-header p, .acik-hesap-header p {
            margin: 0;
            color: #6c757d;
            font-size: 16px;
        }
        
        .account-type-buttons {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .account-type-btn {
            flex: 1;
            padding: 20px;
            border-radius: 15px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            font-size: 18px;
            font-weight: 700;
            transition: all 0.3s ease;
        }
        
        .account-type-btn small {
            font-size: 12px;
            font-weight: 400;
            opacity: 0.8;
        }
        
        .account-type-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .veresiye-form {
            margin-bottom: 25px;
        }
        
        .form-input {
            width: 100%;
            padding: 15px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 16px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
        }
        
        .hesap-list {
            max-height: 300px;
            overflow-y: auto;
            margin-bottom: 25px;
        }
        
        .hesap-item {
            padding: 15px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .hesap-item:hover {
            border-color: #667eea;
            background: #f8f9ff;
        }
        
        .hesap-item.selected {
            border-color: #28a745;
            background: #d4edda;
        }
        
        .hesap-name {
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .hesap-info {
            font-size: 14px;
            color: #6c757d;
        }
        
        .account-actions, .veresiye-actions, .acik-hesap-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
        }
        
        .account-actions .btn, .veresiye-actions .btn, .acik-hesap-actions .btn {
            flex: 1;
            max-width: 150px;
        }
        
        .header {
            background: rgba(255,255,255,0.98);
            backdrop-filter: blur(20px);
            padding: 20px 40px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.12);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            z-index: 1000;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }
        
        .header h1 {
            font-size: 32px;
            background: linear-gradient(135deg, #8B4513, #D2691E);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: flex;
            align-items: center;
            gap: 15px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }
        
        .header-controls {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .btn {
            padding: 14px 28px;
            border: none;
            border-radius: 16px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            touch-action: manipulation;
            position: relative;
            overflow: hidden;
            letter-spacing: 0.5px;
        }
        
        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.6s ease;
        }
        
        .btn:hover::before {
            left: 100%;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2, #f093fb);
            background-size: 200% 200%;
            color: white;
            box-shadow: 0 8px 25px rgba(102,126,234,0.4);
            animation: gradientMove 3s ease infinite;
        }
        

        
        .btn-primary:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 12px 35px rgba(102,126,234,0.6);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #11998e, #38ef7d, #56ab2f);
            background-size: 200% 200%;
            color: white;
            box-shadow: 0 8px 25px rgba(17,153,142,0.4);
        }
        
        .btn-success:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 12px 35px rgba(17,153,142,0.6);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #ff6b6b, #ee5a52, #ff4757);
            background-size: 200% 200%;
            color: white;
            box-shadow: 0 8px 25px rgba(255,107,107,0.4);
            animation: gradientMove 3s ease infinite;
        }
        
        .btn-danger:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 12px 35px rgba(255,107,107,0.6);
        }
        
        .btn-info {
            background: linear-gradient(135deg, #8B4513, #D2691E);
            background-size: 200% 200%;
            color: white;
            box-shadow: 0 8px 25px rgba(139,69,19,0.4);
        }
        
        .btn-info:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 12px 35px rgba(139,69,19,0.6);
        }
        
        .btn-warning {
            background: linear-gradient(135deg, #DC143C, #B22222);
            background-size: 200% 200%;
            color: white;
            font-weight: 700;
            border: none;
            padding: 12px 24px;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(220,20,60,0.4);
        }
        
        .btn-warning::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }
        
        .btn-warning:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 12px 35px rgba(255,193,7,0.6);
        }
        
        .btn-warning:hover::before {
            left: 100%;
        }
        
        .main-container {
            display: flex;
            height: calc(100vh - 80px);
            gap: 0;
        }
        
        .products-panel {
            flex: 2;
            background: rgba(255,255,255,0.98);
            backdrop-filter: blur(25px);
            overflow-y: auto;
            border-right: 1px solid rgba(255,255,255,0.2);
            position: relative;
        }
        
        .products-panel::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(102,126,234,0.02) 0%, rgba(240,147,251,0.02) 100%);
            pointer-events: none;
        }
        
        .bill-panel {
            flex: 1;
            background: rgba(255,255,255,0.99);
            backdrop-filter: blur(30px);
            display: flex;
            flex-direction: column;
            position: relative;
            box-shadow: -5px 0 25px rgba(0,0,0,0.1);
        }
        
        .bill-panel::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(102,126,234,0.03) 0%, rgba(118,75,162,0.03) 100%);
            pointer-events: none;
        }
        
        .categories-nav {
            display: flex;
            padding: 20px;
            gap: 10px;
            border-bottom: 1px solid rgba(0,0,0,0.1);
            overflow-x: auto;
            scrollbar-width: none;
        }
        
        .categories-nav::-webkit-scrollbar {
            display: none;
        }
        
        .category-btn {
            padding: 18px 30px;
            background: linear-gradient(135deg, #ffffff, #f8f9fa);
            border: 2px solid rgba(139,69,19,0.1);
            border-radius: 20px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            white-space: nowrap;
            min-width: 140px;
            text-align: center;
            color: #495057;
            touch-action: manipulation;
            position: relative;
            overflow: hidden;
            letter-spacing: 0.3px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }
        
        .category-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(139,69,19,0.1), transparent);
            transition: left 0.6s ease;
        }
        
        .category-btn:hover::before {
            left: 100%;
        }
        
        .category-btn.active {
            background: linear-gradient(135deg, #8B4513, #D2691E);
            background-size: 200% 200%;
            color: white;
            border-color: transparent;
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 12px 30px rgba(139,69,19,0.5);
        }
        
        .category-btn:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 8px 25px rgba(139,69,19,0.2);
            border-color: rgba(139,69,19,0.3);
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            padding: 20px;
        }
        
        .product-card {
            background: linear-gradient(135deg, #ffffff, #fafbfc);
            border-radius: 25px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 2px solid rgba(102,126,234,0.08);
            position: relative;
            overflow: hidden;
            touch-action: manipulation;
        }
        
        .product-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 50px rgba(102,126,234,0.15);
            border-color: rgba(102,126,234,0.3);
        }
        
        .product-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(102,126,234,0.08), transparent);
            transition: left 0.6s ease;
        }
        
        .product-card:hover::before {
            left: 100%;
        }
        
        .product-card::after {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(135deg, #667eea, #764ba2, #f093fb);
            border-radius: 25px;
            z-index: -1;
            opacity: 0;
            transition: opacity 0.4s ease;
        }
        
        .product-card:hover::after {
            opacity: 0.1;
        }
        
        .product-image {
            width: 100%;
            height: 140px;
            border-radius: 20px;
            object-fit: cover;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            transition: all 0.4s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .product-card:hover .product-image {
            transform: scale(1.05);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .product-no-image {
            width: 100%;
            height: 140px;
            border-radius: 20px;
            background: linear-gradient(135deg, #667eea, #764ba2, #f093fb);
            background-size: 200% 200%;
            animation: gradientMove 3s ease infinite;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 52px;
            color: white;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(102,126,234,0.3);
            transition: all 0.4s ease;
        }
        
        .product-card:hover .product-no-image {
            transform: scale(1.05);
            box-shadow: 0 8px 25px rgba(102,126,234,0.4);
        }
        
        .product-name {
            font-size: 19px;
            font-weight: 800;
            color: #2c3e50;
            margin-bottom: 10px;
            line-height: 1.3;
            letter-spacing: -0.3px;
        }
        
        .product-description {
            font-size: 14px;
            color: #6c757d;
            margin-bottom: 18px;
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .product-price {
            font-size: 22px;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-align: right;
            position: relative;
        }
        
        .product-price::after {
            content: '₺';
            position: absolute;
            right: -15px;
            top: 0;
            font-size: 16px;
            color: #667eea;
        }
        
        .bill-header {
            padding: 30px;
            border-bottom: none;
            background: linear-gradient(135deg, #667eea, #764ba2, #f093fb);
            background-size: 200% 200%;
            animation: gradientMove 4s ease infinite;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .bill-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, transparent 50%, rgba(255,255,255,0.1) 100%);
            animation: shimmer 3s ease infinite;
        }
        
        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        
        .bill-title {
            font-size: 26px;
            font-weight: 800;
            margin-bottom: 12px;
            letter-spacing: -0.5px;
            position: relative;
            z-index: 2;
        }
        
        .bill-date {
            font-size: 15px;
            opacity: 0.95;
            position: relative;
            z-index: 2;
            font-weight: 500;
        }
        
        .bill-items {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
        }
        
        .bill-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px;
            margin-bottom: 12px;
            background: linear-gradient(135deg, #ffffff, #f8f9fa);
            border-radius: 18px;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 1px solid rgba(102,126,234,0.08);
            position: relative;
            overflow: hidden;
        }
        
        .bill-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(102,126,234,0.05), transparent);
            transition: left 0.6s ease;
        }
        
        .bill-item:hover::before {
            left: 100%;
        }
        
        .bill-item:hover {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            transform: translateX(8px) scale(1.02);
            box-shadow: 0 8px 25px rgba(102,126,234,0.1);
            border-color: rgba(102,126,234,0.2);
        }
        
        .item-info {
            flex: 1;
        }
        
        .item-name {
            font-size: 17px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 6px;
            letter-spacing: -0.2px;
        }
        
        .item-price {
            font-size: 14px;
            color: #6c757d;
            font-weight: 500;
        }
        
        .item-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .quantity-btn {
            width: 40px;
            height: 40px;
            border: none;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex;
            align-items: center;
            justify-content: center;
            touch-action: manipulation;
            position: relative;
            overflow: hidden;
        }
        
        .quantity-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255,255,255,0.2);
            transform: scale(0);
            border-radius: 50%;
            transition: transform 0.3s ease;
        }
        
        .quantity-btn:active::before {
            transform: scale(1);
        }
        
        .quantity-btn.minus {
            background: linear-gradient(135deg, #ff6b6b, #ee5a52, #ff4757);
            background-size: 200% 200%;
            animation: gradientMove 3s ease infinite;
            color: white;
            box-shadow: 0 4px 15px rgba(255,107,107,0.3);
        }
        
        .quantity-btn.plus {
            background: linear-gradient(135deg, #11998e, #38ef7d, #56ab2f);
            background-size: 200% 200%;
            animation: gradientMove 3s ease infinite;
            color: white;
            box-shadow: 0 4px 15px rgba(17,153,142,0.3);
        }
        
        .quantity-btn:hover {
            transform: scale(1.15) rotate(5deg);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }
        
        .quantity {
            font-size: 18px;
            font-weight: 800;
            min-width: 35px;
            text-align: center;
            color: #2c3e50;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 10px;
            padding: 8px 12px;
            border: 1px solid rgba(102,126,234,0.1);
        }
        
        .item-total {
            font-size: 18px;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            min-width: 70px;
            text-align: right;
        }
        
        .bill-footer {
            padding: 30px;
            border-top: none;
            background: linear-gradient(135deg, #ffffff, #f8f9fa);
            position: relative;
        }
        
        .bill-footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 20px;
            right: 20px;
            height: 2px;
            background: linear-gradient(90deg, transparent, #667eea, transparent);
        }
        
        .total-section {
            margin-bottom: 25px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            font-size: 17px;
            font-weight: 600;
            color: #495057;
        }
        
        .total-row.grand-total {
            font-size: 28px;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            padding-top: 20px;
            border-top: 3px solid transparent;
            background-image: linear-gradient(white, white), linear-gradient(135deg, #667eea, #764ba2);
            background-origin: border-box;
            background-clip: padding-box, border-box;
            position: relative;
        }
        
        .total-row.grand-total::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(135deg, #667eea, #764ba2, #f093fb);
            border-radius: 2px;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        
        .action-buttons .btn {
            flex: 1;
            justify-content: center;
        }
        
        .empty-bill {
            text-align: center;
            padding: 60px 30px;
            color: #6c757d;
            position: relative;
        }
        
        .empty-bill::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 200px;
            height: 200px;
            background: linear-gradient(135deg, rgba(102,126,234,0.05), rgba(240,147,251,0.05));
            border-radius: 50%;
            z-index: -1;
        }
        
        .empty-bill-icon {
            font-size: 80px;
            margin-bottom: 25px;
            opacity: 0.6;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: pulse 2s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .empty-bill h3 {
            font-size: 22px;
            font-weight: 700;
            color: #495057;
            margin-bottom: 10px;
        }
        
        .empty-bill p {
            font-size: 16px;
            opacity: 0.8;
            line-height: 1.5;
        }
        
        .manual-entry {
            background: linear-gradient(135deg, #ffffff, #f8f9fa);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 8px 25px rgba(102,126,234,0.1);
            border: 1px solid rgba(102,126,234,0.1);
            position: relative;
            overflow: hidden;
        }
        
        .manual-entry::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(135deg, #667eea, #764ba2, #f093fb);
        }
        
        .manual-entry h3 {
            margin-bottom: 20px;
            color: #2c3e50;
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -0.3px;
        }
        
        .manual-form {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }
        
        .manual-input {
            flex: 1;
            padding: 15px 18px;
            border: 2px solid rgba(102,126,234,0.15);
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            background: rgba(255,255,255,0.8);
        }
        
        .manual-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
            background: white;
            transform: translateY(-1px);
        }
        
        .manual-input.price {
            flex: 0 0 120px;
            text-align: center;
            font-weight: 700;
        }
        
        /* Klavye Modal Stilleri */
        .keyboard-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }
        
        .keyboard-container {
            background: linear-gradient(135deg, #ffffff, #f8f9fa);
            border-radius: 25px;
            padding: 35px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 450px;
            width: 90%;
            border: 1px solid rgba(102,126,234,0.1);
            position: relative;
            overflow: hidden;
        }
        
        .keyboard-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #667eea, #764ba2, #f093fb);
        }
        
        .keyboard-header {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .keyboard-header h3 {
            color: #2c3e50;
            font-size: 26px;
            font-weight: 800;
            margin-bottom: 15px;
            letter-spacing: -0.5px;
        }
        
        .keyboard-display {
            background: linear-gradient(135deg, #f8f9fa, #ffffff);
            border: 2px solid rgba(102,126,234,0.2);
            border-radius: 15px;
            padding: 20px;
            font-size: 28px;
            font-weight: 700;
            text-align: right;
            margin-bottom: 25px;
            min-height: 60px;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            box-shadow: inset 0 2px 10px rgba(0,0,0,0.05);
            color: #2c3e50;
        }
        
        .keyboard-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
            margin-bottom: 25px;
        }
        
        .keyboard-btn {
            padding: 22px;
            border: 2px solid rgba(102,126,234,0.15);
            border-radius: 15px;
            font-size: 22px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            background: linear-gradient(135deg, #ffffff, #f8f9fa);
            color: #2c3e50;
            box-shadow: 0 4px 15px rgba(102,126,234,0.1);
            position: relative;
            overflow: hidden;
            min-height: 70px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .keyboard-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(102,126,234,0.1), transparent);
            transition: left 0.5s ease;
        }
        
        .keyboard-btn:hover {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(102,126,234,0.3);
            border-color: #667eea;
        }
        
        .keyboard-btn:hover::before {
            left: 100%;
        }
        
        .keyboard-btn:active {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(102,126,234,0.2);
        }
        
        .keyboard-btn.zero {
            grid-column: span 2;
        }
        
        .keyboard-btn.clear {
            background: linear-gradient(135deg, #ff6b6b, #ee5a52);
            color: white;
            border-color: #ff6b6b;
        }
        
        .keyboard-btn.clear:hover {
            background: linear-gradient(135deg, #ff5252, #e53e3e);
            box-shadow: 0 8px 25px rgba(255,107,107,0.4);
        }
        
        .keyboard-btn.decimal {
            background: linear-gradient(135deg, #17a2b8, #20c997);
            color: white;
            border-color: #17a2b8;
        }
        
        .keyboard-btn.decimal:hover {
            background: linear-gradient(135deg, #138496, #1ea085);
            box-shadow: 0 8px 25px rgba(23,162,184,0.4);
        }
        
        .keyboard-actions {
            display: flex;
            gap: 18px;
        }
        
        .keyboard-actions .btn {
            flex: 1;
            padding: 18px 25px;
            border-radius: 15px;
            font-size: 18px;
            font-weight: 700;
            position: relative;
            overflow: hidden;
        }
        
        .keyboard-actions .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }
        
        .keyboard-actions .btn:hover::before {
            left: 100%;
        }
        
        /* İptal Modal Stilleri */
        .cancel-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(10px);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }
        
        .cancel-container {
            background: linear-gradient(135deg, #ffffff, #f8f9fa);
            border-radius: 25px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 450px;
            width: 90%;
            border: 1px solid rgba(102,126,234,0.1);
            position: relative;
            overflow: hidden;
            text-align: center;
        }
        
        .cancel-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #ffc107, #fd7e14, #ff8c00);
        }
        
        .cancel-header h3 {
            color: #2c3e50;
            font-size: 26px;
            font-weight: 800;
            margin-bottom: 10px;
            letter-spacing: -0.5px;
        }
        
        .cancel-header p {
            color: #6c757d;
            font-size: 16px;
            margin-bottom: 25px;
        }
        
        .password-section {
            margin-bottom: 30px;
        }
        
        .password-input {
            width: 100%;
            padding: 18px 25px;
            border: 2px solid rgba(102,126,234,0.15);
            border-radius: 15px;
            font-size: 18px;
            font-weight: 600;
            text-align: center;
            transition: all 0.3s ease;
            background: rgba(255,255,255,0.8);
            letter-spacing: 2px;
        }
        
        .password-input:focus {
            outline: none;
            border-color: #ffc107;
            box-shadow: 0 0 0 3px rgba(255,193,7,0.1);
            background: white;
            transform: translateY(-2px);
        }
        
        .password-error {
            color: #dc3545;
            font-size: 14px;
            font-weight: 600;
            margin-top: 10px;
            display: none;
        }
        
        .cancel-actions {
            display: flex;
            gap: 15px;
        }
        
        /* Adisyon Listesi Modal */
        .bills-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(10px);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }
        
        .bills-container {
            background: linear-gradient(135deg, #ffffff, #f8f9fa);
            border-radius: 25px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            border: 1px solid rgba(102,126,234,0.1);
            position: relative;
            overflow: hidden;
        }
        
        .bills-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #667eea, #764ba2, #f093fb);
        }
        
        .bills-header {
            text-align: center;
            margin-bottom: 25px;
        }
        
        .bills-header h3 {
            color: #2c3e50;
            font-size: 26px;
            font-weight: 800;
            margin-bottom: 10px;
            letter-spacing: -0.5px;
        }
        
        .bills-header p {
            color: #6c757d;
            font-size: 16px;
        }
        
        .bills-list {
            max-height: 400px;
            overflow-y: auto;
            margin-bottom: 25px;
            padding: 10px;
        }
        
        .bill-item {
            background: linear-gradient(135deg, #ffffff, #f8f9fa);
            border: 2px solid rgba(102,126,234,0.1);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .bill-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(102,126,234,0.1), transparent);
            transition: left 0.5s ease;
        }
        
        .bill-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102,126,234,0.2);
            border-color: rgba(102,126,234,0.3);
        }
        
        .bill-item:hover::before {
            left: 100%;
        }
        
        .bill-item.selected {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            border-color: #dc3545;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(220,53,69,0.3);
        }
        
        .bill-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .bill-details h4 {
            margin: 0 0 5px 0;
            font-size: 18px;
            font-weight: 700;
        }
        
        .bill-details p {
            margin: 0;
            font-size: 14px;
            opacity: 0.8;
        }
        
        .bill-total {
            font-size: 20px;
            font-weight: 800;
            color: #28a745;
        }
        
        .bill-item.selected .bill-total {
            color: white;
        }
        
        .bills-actions {
            display: flex;
            gap: 15px;
        }
        
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        @media (max-width: 1024px) {
            .main-container {
                flex-direction: column;
                gap: 20px;
            }
            
            .products-panel {
                flex: 1;
                height: 60vh;
                border-radius: 20px;
            }
            
            .bill-panel {
                flex: 1;
                height: 40vh;
                border-radius: 20px;
            }
            
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
                gap: 18px;
                padding: 20px;
            }
            
            .keyboard-container {
                max-width: 380px;
                padding: 30px;
            }
        }
        
        @media (max-width: 768px) {
            .header {
                padding: 15px 20px;
            }
            
            .header h1 {
                font-size: 22px;
                gap: 8px;
            }
            
            .products-panel, .bill-panel {
                border-radius: 15px;
            }
            
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
                gap: 15px;
                padding: 15px;
            }
            
            .product-card {
                border-radius: 15px;
            }
            
            .keyboard-container {
                max-width: 320px;
                padding: 25px;
                border-radius: 20px;
            }
            
            .keyboard-btn {
                padding: 18px;
                font-size: 20px;
                min-height: 60px;
            }
            
            .manual-entry {
                padding: 20px;
                border-radius: 15px;
            }
                padding: 10px;
            }
            
            .product-card {
                padding: 15px;
            }
            
            .categories-nav {
                padding: 15px;
            }
            
            .category-btn {
                padding: 12px 20px;
                font-size: 14px;
                min-width: 100px;
            }
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .pulse {
            animation: pulse 0.3s ease-in-out;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .slide-in {
            animation: slideIn 0.3s ease-out;
        }
        
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>
            <span>🧾</span>
            Adisyon Sistemi
        </h1>
        <div class="header-controls">
            <span>Hoş geldin, <?php echo htmlspecialchars($_SESSION['admin_adi']); ?></span>
            <a href="panel.php" class="btn btn-primary">📊 Panel</a>
            <a href="urunler.php" class="btn btn-primary">🍽️ Ürünler</a>
            <a href="kategoriler.php" class="btn btn-primary">📂 Kategoriler</a>
            <a href="raporlar.php" class="btn btn-primary">📈 Raporlar</a>
            <a href="ayarlar.php" class="btn btn-primary">⚙️ Ayarlar</a>
            <a href="cikis.php" class="btn btn-danger">🚪 Çıkış</a>
        </div>
    </div>
    
    <div class="main-container">
        <div class="products-panel">
            <div class="categories-nav">
                <button class="category-btn active" onclick="showCategory('all')" data-category="all">
                    🍽️ Tümü
                </button>
                <?php foreach ($kategoriler as $kategori): ?>
                    <button class="category-btn" onclick="showCategory(<?php echo $kategori['id']; ?>)" data-category="<?php echo $kategori['id']; ?>">
                        📂 <?php echo htmlspecialchars($kategori['kategori_adi']); ?>
                    </button>
                <?php endforeach; ?>
            </div>
            
            <div class="products-grid" id="products-grid">
                <?php foreach ($urunler as $urun): ?>
                    <div class="product-card fade-in" data-category="<?php echo $urun['kategori_id']; ?>" onclick="addToBill(<?php echo $urun['id']; ?>, '<?php echo htmlspecialchars($urun['urun_adi'], ENT_QUOTES); ?>', <?php echo $urun['fiyat'] ?? 0; ?>)">
                        <?php if (!empty($urun['gorsel_url']) && file_exists('../' . $urun['gorsel_url'])): ?>
                            <img src="../<?php echo htmlspecialchars($urun['gorsel_url']); ?>" alt="<?php echo htmlspecialchars($urun['urun_adi']); ?>" class="product-image">
                        <?php else: ?>
                            <div class="product-no-image">
                                🍽️
                            </div>
                        <?php endif; ?>
                        
                        <div class="product-name"><?php echo htmlspecialchars($urun['urun_adi']); ?></div>
                        
                        <?php if (!empty($urun['aciklama'])): ?>
                            <div class="product-description"><?php echo htmlspecialchars($urun['aciklama']); ?></div>
                        <?php endif; ?>
                        
                        <div class="product-price"><?php echo number_format($urun['fiyat'] ?? 0, 2); ?> ₺</div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="bill-panel">
            <div class="bill-header">
                <div class="bill-title">🧾 Adisyon</div>
                <div class="bill-date" id="bill-date"></div>
            </div>
            
            <div class="manual-entry">
                <h3>📝 Manuel Giriş</h3>
                <div class="manual-form">
                    <input type="text" class="manual-input" id="manual-name" placeholder="Ürün adı...">
                    <input type="number" class="manual-input price" id="manual-price" placeholder="Fiyat" step="0.01" readonly>
                    <button class="btn btn-info" onclick="openKeyboard()">🔢 Klavye Aç</button>
                    <button class="btn btn-success" onclick="addManualItem()">➕ Ekle</button>
                </div>
            </div>
            
            <div class="bill-items" id="bill-items">
                <div class="empty-bill">
                    <div class="empty-bill-icon">🛒</div>
                    <p>Henüz ürün eklenmedi</p>
                    <p>Ürünlere tıklayarak adisyona ekleyin</p>
                </div>
            </div>
            
            <div class="bill-footer">
                <div class="total-section">
                    <div class="total-row">
                        <span>Ara Toplam:</span>
                        <span id="subtotal">0.00 ₺</span>
                    </div>
                    <div class="total-row grand-total">
                        <span>TOPLAM:</span>
                        <span id="total">0.00 ₺</span>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <button class="btn btn-danger" onclick="clearBill()">🗑️ Temizle</button>
                    <button class="btn btn-warning" onclick="openCancelModal()">❌ İptal</button>
                    <button class="btn btn-info" onclick="openAccountModal()">💳 Hesaba Ekle</button>
                    <button class="btn btn-success" onclick="printBill()">🖨️ Yazdır</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Klavye Modal -->
    <div class="keyboard-modal" id="keyboard-modal">
        <div class="keyboard-container">
            <div class="keyboard-header">
                <h3>🔢 Sayısal Klavye</h3>
                <div class="keyboard-display" id="keyboard-display">0</div>
            </div>
            
            <div class="keyboard-grid">
                <button class="keyboard-btn" onclick="addDigit('7')">7</button>
                <button class="keyboard-btn" onclick="addDigit('8')">8</button>
                <button class="keyboard-btn" onclick="addDigit('9')">9</button>
                
                <button class="keyboard-btn" onclick="addDigit('4')">4</button>
                <button class="keyboard-btn" onclick="addDigit('5')">5</button>
                <button class="keyboard-btn" onclick="addDigit('6')">6</button>
                
                <button class="keyboard-btn" onclick="addDigit('1')">1</button>
                <button class="keyboard-btn" onclick="addDigit('2')">2</button>
                <button class="keyboard-btn" onclick="addDigit('3')">3</button>
                
                <button class="keyboard-btn zero" onclick="addDigit('0')">0</button>
                <button class="keyboard-btn decimal" onclick="addDecimal()">.</button>
            </div>
            
            <div class="keyboard-grid" style="grid-template-columns: repeat(2, 1fr); margin-bottom: 20px;">
                <button class="keyboard-btn clear" onclick="clearKeyboard()">C</button>
                <button class="keyboard-btn clear" onclick="backspace()">⌫</button>
            </div>
            
            <div class="keyboard-actions">
                <button class="btn btn-danger" onclick="closeKeyboard()">❌ İptal</button>
                <button class="btn btn-success" onclick="confirmPrice()">✅ Tamam</button>
            </div>
        </div>
    </div>
    
    <!-- İptal Modal -->
    <div class="cancel-modal" id="cancel-modal">
        <div class="cancel-container">
            <div class="cancel-header">
                <h3>🔐 Şifre Gerekli</h3>
                <p>Adisyon iptal işlemi için şifre giriniz</p>
            </div>
            
            <div class="password-section">
                <input type="password" class="password-input" id="cancel-password" placeholder="Şifre giriniz..." maxlength="20">
                <div class="password-error" id="password-error">Hatalı şifre!</div>
            </div>
            
            <div class="cancel-actions">
                <button class="btn btn-secondary" onclick="closeCancelModal()">❌ İptal</button>
                <button class="btn btn-warning" onclick="checkPassword()">🔓 Devam Et</button>
            </div>
        </div>
    </div>
    
    <!-- Adisyon Listesi Modal -->
    <div class="bills-modal" id="bills-modal">
        <div class="bills-container">
            <div class="bills-header">
                <h3>🧾 Aktif Adisyonlar</h3>
                <p>Silmek istediğiniz adisyonları seçin</p>
            </div>
            
            <div class="bills-list" id="bills-list">
                <!-- Adisyonlar buraya yüklenecek -->
            </div>
            
            <div class="bills-actions">
                <button class="btn btn-secondary" onclick="closeBillsModal()">❌ İptal</button>
                <button class="btn btn-danger" onclick="deleteSelectedBills()" id="delete-btn" disabled>🗑️ Seçilenleri Sil</button>
            </div>
        </div>
    </div>
    
    <!-- Hesap Tipi Seçim Modal -->
    <div class="account-modal" id="account-modal">
        <div class="account-container">
            <div class="account-header">
                <h3>💳 Hesap Tipi Seçin</h3>
                <p>Adisyonu hangi hesap tipine eklemek istiyorsunuz?</p>
            </div>
            
            <div class="account-type-buttons">
                <button class="btn btn-primary account-type-btn" onclick="selectAccountType('veresiye')">
                    📝 Veresiye
                    <small>Müşteri bilgileri ile</small>
                </button>
                <button class="btn btn-success account-type-btn" onclick="selectAccountType('acik_hesap')">
                    🏢 Açık Hesap
                    <small>Kurumsal hesaplar</small>
                </button>
            </div>
            
            <div class="account-actions">
                <button class="btn btn-secondary" onclick="closeAccountModal()">❌ İptal</button>
            </div>
        </div>
    </div>
    
    <!-- Veresiye Modal -->
    <div class="veresiye-modal" id="veresiye-modal">
        <div class="veresiye-container">
            <div class="veresiye-header">
                <h3>📝 Veresiye Bilgileri</h3>
                <p>Müşteri bilgilerini girin (opsiyonel)</p>
            </div>
            
            <div class="veresiye-form">
                <input type="text" class="form-input" id="veresiye-name" placeholder="Ad Soyad">
                <input type="tel" class="form-input" id="veresiye-phone" placeholder="Telefon">
                <textarea class="form-input" id="veresiye-note" placeholder="Not (opsiyonel)" rows="3"></textarea>
            </div>
            
            <div class="veresiye-actions">
                <button class="btn btn-secondary" onclick="closeVeresiyeModal()">❌ İptal</button>
                <button class="btn btn-success" onclick="addToVeresiye()">✅ Veresiye Ekle</button>
            </div>
        </div>
    </div>
    
    <!-- Açık Hesap Seçim Modal -->
    <div class="acik-hesap-modal" id="acik-hesap-modal">
        <div class="acik-hesap-container">
            <div class="acik-hesap-header">
                <h3>🏢 Açık Hesap Seçin</h3>
                <p>Adisyonu eklemek istediğiniz hesabı seçin</p>
            </div>
            
            <div class="hesap-list" id="hesap-list">
                <!-- Hesaplar buraya yüklenecek -->
            </div>
            
            <div class="acik-hesap-actions">
                <button class="btn btn-secondary" onclick="closeAcikHesapModal()">❌ İptal</button>
            </div>
        </div>
    </div>
    
    <script>
        let billItems = [];
        let itemCounter = 0;
        
        // Tarih güncelleme
        function updateDate() {
            const now = new Date();
            const dateStr = now.toLocaleDateString('tr-TR', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
            document.getElementById('bill-date').textContent = dateStr;
        }
        
        // Kategori gösterme
        function showCategory(categoryId) {
            const products = document.querySelectorAll('.product-card');
            const buttons = document.querySelectorAll('.category-btn');
            
            // Buton aktiflik durumu
            buttons.forEach(btn => {
                btn.classList.remove('active');
                if (btn.dataset.category == categoryId) {
                    btn.classList.add('active');
                }
            });
            
            // Ürün gösterme/gizleme
            products.forEach(product => {
                if (categoryId === 'all' || product.dataset.category == categoryId) {
                    product.style.display = 'block';
                    product.classList.add('fade-in');
                } else {
                    product.style.display = 'none';
                }
            });
        }
        
        // Adisyona ürün ekleme
        function addToBill(id, name, price) {
            const existingItem = billItems.find(item => item.id === id);
            
            if (existingItem) {
                existingItem.quantity += 1;
                existingItem.total = existingItem.quantity * existingItem.price;
            } else {
                billItems.push({
                    id: id,
                    name: name,
                    price: price,
                    quantity: 1,
                    total: price
                });
            }
            
            updateBillDisplay();
            
            // Görsel feedback
            const productCard = event.target.closest('.product-card');
            if (productCard) {
                productCard.classList.add('pulse');
                setTimeout(() => productCard.classList.remove('pulse'), 300);
            }
        }
        
        // Manuel ürün ekleme
        function addManualItem() {
            const name = document.getElementById('manual-name').value.trim();
            const price = parseFloat(document.getElementById('manual-price').value);
            
            if (!name || !price || price <= 0) {
                alert('Lütfen geçerli bir ürün adı ve fiyat girin!');
                return;
            }
            
            billItems.push({
                id: 'manual_' + (++itemCounter),
                name: name,
                price: price,
                quantity: 1,
                total: price
            });
            
            // Formu temizle
            document.getElementById('manual-name').value = '';
            document.getElementById('manual-price').value = '';
            
            updateBillDisplay();
        }
        
        // Miktar değiştirme
        function changeQuantity(id, change) {
            const item = billItems.find(item => item.id === id);
            if (item) {
                item.quantity += change;
                if (item.quantity <= 0) {
                    removeFromBill(id);
                } else {
                    item.total = item.quantity * item.price;
                    updateBillDisplay();
                }
            }
        }
        
        // Ürün silme
        function removeFromBill(id) {
            billItems = billItems.filter(item => item.id !== id);
            updateBillDisplay();
        }
        
        // Adisyon görünümünü güncelleme
        function updateBillDisplay() {
            const billItemsContainer = document.getElementById('bill-items');
            
            if (billItems.length === 0) {
                billItemsContainer.innerHTML = `
                    <div class="empty-bill">
                        <div class="empty-bill-icon">🛒</div>
                        <p>Henüz ürün eklenmedi</p>
                        <p>Ürünlere tıklayarak adisyona ekleyin</p>
                    </div>
                `;
            } else {
                billItemsContainer.innerHTML = billItems.map(item => `
                    <div class="bill-item slide-in">
                        <div class="item-info">
                            <div class="item-name">${item.name}</div>
                            <div class="item-price">${item.price.toFixed(2)} ₺</div>
                        </div>
                        <div class="item-controls">
                            <button class="quantity-btn minus" onclick="changeQuantity('${item.id}', -1)">−</button>
                            <span class="quantity">${item.quantity}</span>
                            <button class="quantity-btn plus" onclick="changeQuantity('${item.id}', 1)">+</button>
                        </div>
                        <div class="item-total">${item.total.toFixed(2)} ₺</div>
                    </div>
                `).join('');
            }
            
            updateTotals();
        }
        
        // Toplam hesaplama
        function updateTotals() {
            const total = billItems.reduce((sum, item) => sum + item.total, 0);
            
            document.getElementById('subtotal').textContent = total.toFixed(2) + ' ₺';
            document.getElementById('total').textContent = total.toFixed(2) + ' ₺';
        }
        
        // Adisyon temizleme
        function clearBill() {
            if (billItems.length > 0 && confirm('Adisyonu temizlemek istediğinizden emin misiniz?')) {
                billItems = [];
                updateBillDisplay();
            }
        }
        
        // Satış verilerini kaydetme fonksiyonu
        function saveSaleData(billData, total, adisyonNo) {
            const saleData = {
                tarih: new Date().toISOString().split('T')[0],
                saat: new Date().toTimeString().split(' ')[0],
                toplam_tutar: total,
                urun_sayisi: billData.reduce((sum, item) => sum + item.quantity, 0),
                adisyon_no: adisyonNo || 'ADI' + Date.now(),
                items: billData
            };
            
            // AJAX ile satış verilerini kaydet
            fetch('save_sale.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(saleData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Satış verileri kaydedildi:', data.sale_id);
                } else {
                    console.error('Satış kaydedilemedi:', data.error);
                }
            })
            .catch(error => {
                console.error('Satış kaydetme hatası:', error);
            });
        }
        
        // Yazdırma fonksiyonu
        function printBill() {
            if (billItems.length === 0) {
                alert('Adisyonda ürün bulunmuyor!');
                return;
            }
            
            const subtotal = billItems.reduce((sum, item) => sum + item.total, 0);
            const total = subtotal;
            
            // Adisyon numarası oluştur (tarih + saat + rastgele sayı)
            const now = new Date();
            const adisyonNo = now.getFullYear().toString().substr(-2) + 
                             (now.getMonth() + 1).toString().padStart(2, '0') + 
                             now.getDate().toString().padStart(2, '0') + 
                             now.getHours().toString().padStart(2, '0') + 
                             now.getMinutes().toString().padStart(2, '0') + 
                             Math.floor(Math.random() * 100).toString().padStart(2, '0');
            
            // Satış verilerini kaydet
            saveSaleData(billItems, total, adisyonNo);
            
            let printContent = `
                <div style="font-family: Arial, sans-serif; max-width: 300px; margin: 0 auto; padding: 20px;">
                    <div style="text-align: center; margin-bottom: 20px;">
                        <h2 style="margin: 0;">☕ Tiryakideyim</h2>
                        <p style="margin: 5px 0; font-size: 14px;">Adisyon Fişi</p>
                        <p style="margin: 5px 0; font-size: 12px; font-weight: bold;">Adisyon No: ${adisyonNo}</p>
                        <p style="margin: 5px 0; font-size: 12px;">${new Date().toLocaleString('tr-TR')}</p>
                        <hr style="border: 1px dashed #333;">
                    </div>
                    
                    <div style="margin-bottom: 20px;">
            `;
            
            billItems.forEach(item => {
                printContent += `
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px;">
                        <div>
                            <div style="font-weight: bold;">${item.name}</div>
                            <div style="font-size: 12px; color: #666;">${item.quantity} x ${item.price.toFixed(2)} ₺</div>
                        </div>
                        <div style="font-weight: bold;">${item.total.toFixed(2)} ₺</div>
                    </div>
                `;
            });
            
            printContent += `
                    </div>
                    
                    <hr style="border: 1px dashed #333;">
                    
                    <div style="margin-top: 15px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                            <span>Ara Toplam:</span>
                            <span>${subtotal.toFixed(2)} ₺</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 16px; border-top: 1px solid #333; padding-top: 5px;">
                            <span>TOPLAM:</span>
                            <span>${total.toFixed(2)} ₺</span>
                        </div>
                    </div>
                    
                    <div style="text-align: center; margin-top: 20px; font-size: 12px; color: #666;">
                        <p>🙏 Bizi tercih ettiğiniz için teşekkürler!</p>
                    </div>
                </div>
            `;
            
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                    <head>
                        <title>Adisyon Fişi</title>
                        <style>
                            @media print {
                                body { margin: 0; }
                                @page { margin: 0.5cm; }
                            }
                        </style>
                    </head>
                    <body>
                        ${printContent}
                    </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.print();
        }
        
        // Manuel giriş için Enter tuşu desteği
        document.getElementById('manual-name').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('manual-price').focus();
            }
        });
        
        document.getElementById('manual-price').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                addManualItem();
            }
        });
        
        // Klavye değişkenleri
        let keyboardValue = '0';
        let hasDecimal = false;
        
        // Klavye fonksiyonları
        function openKeyboard() {
            keyboardValue = '0';
            hasDecimal = false;
            document.getElementById('keyboard-display').textContent = keyboardValue;
            document.getElementById('keyboard-modal').style.display = 'flex';
        }
        
        function closeKeyboard() {
            document.getElementById('keyboard-modal').style.display = 'none';
        }
        
        function addDigit(digit) {
            if (keyboardValue === '0') {
                keyboardValue = digit;
            } else {
                keyboardValue += digit;
            }
            document.getElementById('keyboard-display').textContent = keyboardValue;
        }
        
        function addDecimal() {
            if (!hasDecimal) {
                if (keyboardValue === '0') {
                    keyboardValue = '0.';
                } else {
                    keyboardValue += '.';
                }
                hasDecimal = true;
                document.getElementById('keyboard-display').textContent = keyboardValue;
            }
        }
        
        function clearKeyboard() {
            keyboardValue = '0';
            hasDecimal = false;
            document.getElementById('keyboard-display').textContent = keyboardValue;
        }
        
        function backspace() {
            if (keyboardValue.length > 1) {
                const lastChar = keyboardValue.slice(-1);
                if (lastChar === '.') {
                    hasDecimal = false;
                }
                keyboardValue = keyboardValue.slice(0, -1);
            } else {
                keyboardValue = '0';
                hasDecimal = false;
            }
            document.getElementById('keyboard-display').textContent = keyboardValue;
        }
        
        function confirmPrice() {
            const price = parseFloat(keyboardValue);
            if (price > 0) {
                document.getElementById('manual-price').value = price.toFixed(2);
                closeKeyboard();
            } else {
                alert('Lütfen geçerli bir fiyat girin!');
            }
        }
        
        // İptal modal fonksiyonları
        function openCancelModal() {
            document.getElementById('cancel-modal').style.display = 'flex';
            document.getElementById('cancel-password').focus();
            document.getElementById('password-error').style.display = 'none';
        }
        
        function closeCancelModal() {
            document.getElementById('cancel-modal').style.display = 'none';
            document.getElementById('cancel-password').value = '';
            document.getElementById('password-error').style.display = 'none';
        }
        
        function checkPassword() {
            const password = document.getElementById('cancel-password').value;
            const correctPassword = 'tiryakim';
            
            if (password === correctPassword) {
                closeCancelModal();
                loadActiveBills();
            } else {
                document.getElementById('password-error').style.display = 'block';
                document.getElementById('cancel-password').value = '';
                document.getElementById('cancel-password').focus();
            }
        }
        
        // Aktif adisyonları yükleme
        function loadActiveBills() {
            document.getElementById('bills-modal').style.display = 'flex';
            
            // AJAX ile gerçek adisyonları yükle
            fetch('get_active_bills.php')
                .then(response => response.json())
                .then(bills => {
                    const billsList = document.getElementById('bills-list');
                    billsList.innerHTML = '';
                    
                    if (bills.length === 0) {
                        billsList.innerHTML = '<div class="no-bills">Aktif adisyon bulunamadı.</div>';
                        return;
                    }
                    
                    bills.forEach(bill => {
                        const billElement = document.createElement('div');
                        billElement.className = 'bill-item';
                        billElement.dataset.billId = bill.id;
                        billElement.onclick = () => toggleBillSelection(bill.id);
                        
                        billElement.innerHTML = `
                            <div class="bill-info">
                                <div class="bill-details">
                                    <h4>${bill.adisyon_no || 'Adisyon #' + bill.id}</h4>
                                    <p>${bill.urun_sayisi} ürün • ${bill.saat}</p>
                                </div>
                                <div class="bill-total">${parseFloat(bill.toplam_tutar).toFixed(2)} ₺</div>
                            </div>
                        `;
                        
                        billsList.appendChild(billElement);
                    });
                })
                .catch(error => {
                    console.error('Adisyonlar yüklenirken hata:', error);
                    document.getElementById('bills-list').innerHTML = '<div class="error">Adisyonlar yüklenirken hata oluştu.</div>';
                });
        }
        
        let selectedBills = [];
        
        function toggleBillSelection(billId) {
            const billElement = document.querySelector(`[data-bill-id="${billId}"]`);
            const index = selectedBills.indexOf(billId);
            
            if (index > -1) {
                selectedBills.splice(index, 1);
                billElement.classList.remove('selected');
            } else {
                selectedBills.push(billId);
                billElement.classList.add('selected');
            }
            
            // Sil butonunu aktif/pasif yap
            const deleteBtn = document.getElementById('delete-btn');
            deleteBtn.disabled = selectedBills.length === 0;
        }
        
        function closeBillsModal() {
            document.getElementById('bills-modal').style.display = 'none';
            selectedBills = [];
            document.getElementById('delete-btn').disabled = true;
        }
        
        function deleteSelectedBills() {
            if (selectedBills.length === 0) return;
            
            const confirmMsg = `${selectedBills.length} adisyon silinecek. Emin misiniz?`;
            if (confirm(confirmMsg)) {
                // AJAX ile silme işlemi
                fetch('delete_bills.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ bill_ids: selectedBills })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(`${data.deleted_count} adisyon başarıyla silindi!`);
                        closeBillsModal();
                    } else {
                        alert('Silme işlemi sırasında hata oluştu: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Silme hatası:', error);
                    alert('Silme işlemi sırasında hata oluştu.');
                });
            }
        }
        
        // Hesap modal fonksiyonları
        function openAccountModal() {
            if (billItems.length === 0) {
                alert('Adisyonda ürün bulunmuyor!');
                return;
            }
            document.getElementById('account-modal').style.display = 'block';
        }
        
        function closeAccountModal() {
            document.getElementById('account-modal').style.display = 'none';
        }
        
        function selectAccountType(type) {
            closeAccountModal();
            
            if (type === 'veresiye') {
                openVeresiyeModal();
            } else if (type === 'acik_hesap') {
                loadAcikHesaplar();
            }
        }
        
        function openVeresiyeModal() {
            document.getElementById('veresiye-modal').style.display = 'block';
        }
        
        function closeVeresiyeModal() {
            document.getElementById('veresiye-modal').style.display = 'none';
            // Formu temizle
            document.getElementById('veresiye-name').value = '';
            document.getElementById('veresiye-phone').value = '';
            document.getElementById('veresiye-note').value = '';
        }
        
        function addToVeresiye() {
            const name = document.getElementById('veresiye-name').value.trim();
            const phone = document.getElementById('veresiye-phone').value.trim();
            const note = document.getElementById('veresiye-note').value.trim();
            
            if (!name) {
                alert('Lütfen müşteri adını girin!');
                return;
            }
            
            const total = billItems.reduce((sum, item) => sum + item.total, 0);
            
            // Önce veresiye müşteri ekle
            const formData = new FormData();
            formData.append('ad_soyad', name);
            formData.append('telefon', phone);
            formData.append('notlar', note);
            
            fetch('hesap_islemler.php?action=add_veresiye', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Hesaba ekle
                    addToAccountProcess('veresiye', data.hesap_id || 0, total, `Veresiye - ${name}`);
                } else {
                    alert('Hata: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Hata:', error);
                alert('İşlem sırasında hata oluştu.');
            });
        }
        
        function loadAcikHesaplar() {
            fetch('hesap_islemler.php?action=get_hesap_list&tip=acik_hesap')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayAcikHesaplar(data.hesaplar);
                    document.getElementById('acik-hesap-modal').style.display = 'block';
                } else {
                    alert('Hesaplar yüklenemedi: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Hata:', error);
                alert('Hesaplar yüklenirken hata oluştu.');
            });
        }
        
        function displayAcikHesaplar(hesaplar) {
            const hesapList = document.getElementById('hesap-list');
            
            if (hesaplar.length === 0) {
                hesapList.innerHTML = '<p style="text-align: center; color: #6c757d;">Kullanılabilir açık hesap bulunamadı.</p>';
                return;
            }
            
            hesapList.innerHTML = hesaplar.map(hesap => `
                <div class="hesap-item" onclick="selectAcikHesap(${hesap.id}, '${hesap.name}')">
                    <div class="hesap-name">${hesap.name}</div>
                    <div class="hesap-info">
                        ${hesap.firma_adi ? hesap.firma_adi + ' - ' : ''}
                        Limit: ${hesap.kullanilabilir_limit.toFixed(2)} ₺
                    </div>
                </div>
            `).join('');
        }
        
        function selectAcikHesap(hesapId, hesapName) {
            const total = billItems.reduce((sum, item) => sum + item.total, 0);
            
            if (confirm(`"${hesapName}" hesabına ${total.toFixed(2)} ₺ tutarında adisyon eklenecek. Onaylıyor musunuz?`)) {
                addToAccountProcess('acik_hesap', hesapId, total, `Açık Hesap - ${hesapName}`);
            }
        }
        
        function closeAcikHesapModal() {
            document.getElementById('acik-hesap-modal').style.display = 'none';
        }
        
        function addToAccountProcess(hesapTipi, hesapId, tutar, aciklama) {
            const formData = new FormData();
            formData.append('hesap_tipi', hesapTipi);
            formData.append('hesap_id', hesapId);
            formData.append('tutar', tutar);
            formData.append('aciklama', aciklama);
            formData.append('adisyon_data', JSON.stringify(billItems));
            
            fetch('hesap_islemler.php?action=add_to_account', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(`Başarılı! Adisyon hesaba eklendi.\nAdisyon No: ${data.adisyon_no}`);
                    clearBill();
                    closeVeresiyeModal();
                    closeAcikHesapModal();
                } else {
                    alert('Hata: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Hata:', error);
                alert('İşlem sırasında hata oluştu.');
            });
        }
        
        // Sayfa yüklendiğinde
        document.addEventListener('DOMContentLoaded', function() {
            updateDate();
            setInterval(updateDate, 60000); // Her dakika güncelle
            
            // Modal dışına tıklandığında kapatma
            document.getElementById('keyboard-modal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeKeyboard();
                }
            });
            
            // Hesap modalları için dışına tıklandığında kapatma
            document.getElementById('account-modal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeAccountModal();
                }
            });
            
            document.getElementById('veresiye-modal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeVeresiyeModal();
                }
            });
            
            document.getElementById('acik-hesap-modal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeAcikHesapModal();
                }
            });
        });
    </script>
</body>
</html>