-- Admin paneline giriş yapacak kullanıcıların bilgilerini tutar.
CREATE TABLE Admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ad_soyad VARCHAR(100) NOT NULL, -- Ad Soyad
    kullanici_adi VARCHAR(50) NOT NULL UNIQUE, -- Kullanıcı Adı (benzersiz olmalı)
    sifre VARCHAR(255) NOT NULL, -- Şifre (düz metin olarak tutulacak)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP -- Kayıt oluşturulma tarihi
);

-- Menüdeki ürün kategorilerini ve alt kategorileri hiyerarşik olarak tutar.
CREATE TABLE Kategoriler (
    id INT PRIMARY KEY AUTO_INCREMENT,
    parent_id INT, -- Eğer bir alt kategori ise, üst kategorisinin ID'si. Ana kategoriler için NULL olur.
    kategori_adi VARCHAR(100) NOT NULL, -- Kategorinin adı (örn: İçecekler, Tatlılar)
    gorunurluk BOOLEAN DEFAULT TRUE, -- Menüde görünüp görünmeyeceği (1: Evet, 0: Hayır)
    sira INT DEFAULT 0, -- Kategorilerin menüdeki sıralaması için
    FOREIGN KEY (parent_id) REFERENCES Kategoriler(id) ON DELETE SET NULL -- Üst kategori silinirse, alt kategori ana kategori olur.
);

-- Menüde yer alan ürünlerin detaylarını tutar.
CREATE TABLE Urunler (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kategori_id INT NOT NULL, -- Ürünün hangi kategoriye ait olduğu
    urun_adi VARCHAR(150) NOT NULL, -- Ürünün adı (örn: Filtre Kahve)
    aciklama TEXT, -- Ürün hakkında detaylı açıklama
    gorsel_url VARCHAR(255), -- Ürün görselinin dosya yolu veya URL'si
    fiyat DECIMAL(10, 2) NOT NULL, -- Ürünün fiyatı
    alerji_uyari TEXT, -- Alerjen maddeler hakkında uyarılar
    kafe_spesiyali BOOLEAN DEFAULT FALSE, -- Kafenin spesiyali mi? (1: Evet, 0: Hayır)
    gorunurluk BOOLEAN DEFAULT TRUE, -- Menüde görünüp görünmeyeceği (1: Evet, 0: Hayır)
    sira INT DEFAULT 0, -- Ürünlerin kategori içindeki sıralaması için
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (kategori_id) REFERENCES Kategoriler(id) ON DELETE CASCADE -- Kategori silinirse, içindeki ürünler de silinir.
);

-- QR menü görünüm ayarlarını tutar.
CREATE TABLE Ayarlar (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ayar_adi VARCHAR(100) NOT NULL UNIQUE, -- Ayar adı (örn: ana_renk, font_ailesi)
    ayar_degeri TEXT NOT NULL, -- Ayar değeri
    aciklama TEXT, -- Ayar hakkında açıklama
    kategori VARCHAR(50) DEFAULT 'genel', -- Ayar kategorisi (renk, font, genel)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Admin kullanıcısı ekleme
INSERT INTO Admins (ad_soyad, kullanici_adi, sifre) VALUES 
('Yönetici', 'admin', '123456');

-- Varsayılan ayarlar ekleme
INSERT INTO Ayarlar (ayar_adi, ayar_degeri, aciklama, kategori) VALUES 
('tema_adi', 'modern_minimalist', 'Aktif tema adı', 'tema'),
('ana_renk', '#8B4513', 'Ana tema rengi', 'renk'),
('ikincil_renk', '#D2691E', 'İkincil tema rengi', 'renk'),
('arkaplan_rengi', '#f5f5f5', 'Arkaplan rengi', 'renk'),
('metin_rengi', '#333333', 'Ana metin rengi', 'renk'),
('font_ailesi', 'Arial, sans-serif', 'Ana font ailesi', 'font'),
('baslik_font_boyutu', '24', 'Başlık font boyutu (px)', 'font'),
('metin_font_boyutu', '16', 'Normal metin font boyutu (px)', 'font'),
('logo_metni', 'Tiryakideyim', 'Menüde görünecek logo metni', 'genel'),
('alt_metin', 'Lezzetli kahveler ve tatlılar', 'Logo altında görünecek açıklama', 'genel'),
('bakim_modu', '0', 'Bakım modu (1: Açık, 0: Kapalı)', 'genel');

-- Tema tanımları tablosu
CREATE TABLE IF NOT EXISTS Temalar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tema_adi VARCHAR(50) NOT NULL UNIQUE,
    tema_baslik VARCHAR(100) NOT NULL,
    aciklama TEXT,
    ana_renk VARCHAR(7) NOT NULL,
    ikincil_renk VARCHAR(7) NOT NULL,
    arkaplan_rengi VARCHAR(7) NOT NULL,
    metin_rengi VARCHAR(7) NOT NULL,
    font_ailesi VARCHAR(100) NOT NULL,
    baslik_font_boyutu INT NOT NULL,
    metin_font_boyutu INT NOT NULL,
    ozel_css TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Varsayılan temalar ekleme
INSERT INTO Temalar (tema_adi, tema_baslik, aciklama, ana_renk, ikincil_renk, arkaplan_rengi, metin_rengi, font_ailesi, baslik_font_boyutu, metin_font_boyutu, ozel_css) VALUES 
('modern_minimalist', 'Modern Minimalist', 'Sade ve modern tasarım, temiz çizgiler', '#2C3E50', '#3498DB', '#FFFFFF', '#2C3E50', 'Roboto, sans-serif', 28, 16, '.category-card { border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); } .product-card { border: none; border-radius: 12px; }'),
('klasik_restoran', 'Klasik Restoran', 'Geleneksel restoran atmosferi, sıcak renkler', '#8B4513', '#D2691E', '#FFF8DC', '#4A4A4A', 'Playfair Display, serif', 32, 18, '.category-card { background: linear-gradient(145deg, #FFF8DC, #F5DEB3); border: 2px solid #D2691E; } .product-card { background: #FFFAF0; border-left: 4px solid #D2691E; }'),
('vintage_kahvehane', 'Vintage Kahvehane', 'Nostaljik kahvehane teması, vintage dokular', '#5D4037', '#8D6E63', '#F3E5AB', '#3E2723', 'Dancing Script, cursive', 36, 17, '.category-card { background: radial-gradient(circle, #F3E5AB, #E6D7A3); border: 3px dashed #8D6E63; } .product-card { background: #FFF9C4; box-shadow: inset 0 0 10px rgba(139,69,19,0.1); }'),
('luks_boutique', 'Lüks Boutique', 'Şık ve lüks görünüm, premium hissi', '#1A1A1A', '#C9B037', '#F8F8F8', '#1A1A1A', 'Montserrat, sans-serif', 30, 16, '.category-card { background: linear-gradient(135deg, #F8F8F8, #E8E8E8); border: 1px solid #C9B037; box-shadow: 0 8px 25px rgba(0,0,0,0.15); } .product-card { background: #FFFFFF; border-top: 3px solid #C9B037; }');