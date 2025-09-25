-- Hesap Yönetimi için Veritabanı Tabloları
-- Oluşturulma Tarihi: 2024
-- Veresiye ve Açık Hesap Sistemi

-- Açık hesaplar tablosu (Kurumsal müşteriler, düzenli müşteriler)
CREATE TABLE IF NOT EXISTS acik_hesaplar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hesap_adi VARCHAR(255) NOT NULL,
    firma_adi VARCHAR(255),
    yetkili_kisi VARCHAR(255),
    telefon VARCHAR(20),
    email VARCHAR(255),
    adres TEXT,
    kredi_limiti DECIMAL(10,2) DEFAULT 0.00,
    mevcut_borc DECIMAL(10,2) DEFAULT 0.00,
    durum ENUM('aktif', 'pasif', 'dondurulmus') DEFAULT 'aktif',
    notlar TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Veresiye müşteriler tablosu (Bireysel müşteriler)
CREATE TABLE IF NOT EXISTS veresiye_musteriler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad_soyad VARCHAR(255) NOT NULL,
    telefon VARCHAR(20),
    adres TEXT,
    mevcut_borc DECIMAL(10,2) DEFAULT 0.00,
    durum ENUM('aktif', 'pasif') DEFAULT 'aktif',
    notlar TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Hesap hareketleri tablosu (Hem açık hesap hem veresiye için)
CREATE TABLE IF NOT EXISTS hesap_hareketleri (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hesap_tipi ENUM('acik_hesap', 'veresiye') NOT NULL,
    hesap_id INT NOT NULL, -- acik_hesaplar.id veya veresiye_musteriler.id
    hareket_tipi ENUM('borc', 'alacak', 'odeme') NOT NULL,
    tutar DECIMAL(10,2) NOT NULL,
    aciklama TEXT,
    adisyon_id INT, -- satislar tablosuna referans
    tarih DATE NOT NULL,
    saat TIME NOT NULL,
    kullanici_id INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_hesap_tipi_id (hesap_tipi, hesap_id),
    INDEX idx_tarih (tarih),
    INDEX idx_adisyon (adisyon_id)
);

-- Hesap ödemeleri tablosu
CREATE TABLE IF NOT EXISTS hesap_odemeleri (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hesap_tipi ENUM('acik_hesap', 'veresiye') NOT NULL,
    hesap_id INT NOT NULL,
    odeme_tutari DECIMAL(10,2) NOT NULL,
    odeme_tarihi DATE NOT NULL,
    odeme_saati TIME NOT NULL,
    odeme_yontemi ENUM('nakit', 'kart', 'havale', 'diger') DEFAULT 'nakit',
    aciklama TEXT,
    kullanici_id INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_hesap_tipi_id (hesap_tipi, hesap_id),
    INDEX idx_odeme_tarihi (odeme_tarihi)
);

-- Satışlar tablosuna hesap bilgisi ekleme
ALTER TABLE satislar 
ADD COLUMN hesap_tipi ENUM('nakit', 'acik_hesap', 'veresiye') DEFAULT 'nakit',
ADD COLUMN hesap_id INT NULL,
ADD INDEX idx_hesap_tipi_id (hesap_tipi, hesap_id);

-- Örnek açık hesaplar
INSERT INTO acik_hesaplar (hesap_adi, firma_adi, yetkili_kisi, telefon, kredi_limiti, mevcut_borc) VALUES 
('Merkez Okul', 'Atatürk İlkokulu', 'Müdür Ahmet Bey', '0532-123-4567', 5000.00, 0.00),
('Belediye Kantini', 'Şehir Belediyesi', 'Kantin Sorumlusu', '0533-987-6543', 10000.00, 0.00),
('Özel Hastane', 'Sağlık Hastanesi', 'İdari İşler', '0534-555-1234', 15000.00, 0.00);

-- Örnek veresiye müşteriler
INSERT INTO veresiye_musteriler (ad_soyad, telefon, mevcut_borc) VALUES 
('Mehmet Yılmaz', '0535-111-2233', 0.00),
('Ayşe Kaya', '0536-444-5566', 0.00),
('Ali Demir', '0537-777-8899', 0.00);

-- Hesap özet view'ları
CREATE OR REPLACE VIEW v_acik_hesap_ozet AS
SELECT 
    ah.id,
    ah.hesap_adi,
    ah.firma_adi,
    ah.kredi_limiti,
    ah.mevcut_borc,
    (ah.kredi_limiti - ah.mevcut_borc) as kullanilabilir_limit,
    ah.durum,
    COUNT(hh.id) as hareket_sayisi,
    MAX(hh.created_at) as son_hareket_tarihi
FROM acik_hesaplar ah
LEFT JOIN hesap_hareketleri hh ON ah.id = hh.hesap_id AND hh.hesap_tipi = 'acik_hesap'
GROUP BY ah.id;

CREATE OR REPLACE VIEW v_veresiye_ozet AS
SELECT 
    vm.id,
    vm.ad_soyad,
    vm.telefon,
    vm.mevcut_borc,
    vm.durum,
    COUNT(hh.id) as hareket_sayisi,
    MAX(hh.created_at) as son_hareket_tarihi
FROM veresiye_musteriler vm
LEFT JOIN hesap_hareketleri hh ON vm.id = hh.hesap_id AND hh.hesap_tipi = 'veresiye'
GROUP BY vm.id;

-- Günlük hesap raporu view'ı
CREATE OR REPLACE VIEW v_gunluk_hesap_raporu AS
SELECT 
    DATE(hh.tarih) as tarih,
    hh.hesap_tipi,
    COUNT(*) as islem_sayisi,
    SUM(CASE WHEN hh.hareket_tipi = 'borc' THEN hh.tutar ELSE 0 END) as toplam_borc,
    SUM(CASE WHEN hh.hareket_tipi = 'alacak' THEN hh.tutar ELSE 0 END) as toplam_alacak,
    SUM(CASE WHEN hh.hareket_tipi = 'odeme' THEN hh.tutar ELSE 0 END) as toplam_odeme
FROM hesap_hareketleri hh
GROUP BY DATE(hh.tarih), hh.hesap_tipi
ORDER BY tarih DESC;

-- Trigger: Hesap hareketlerinde borç güncellemesi
DELIMITER //
CREATE TRIGGER tr_hesap_hareket_after_insert
AFTER INSERT ON hesap_hareketleri
FOR EACH ROW
BEGIN
    IF NEW.hesap_tipi = 'acik_hesap' THEN
        IF NEW.hareket_tipi = 'borc' THEN
            UPDATE acik_hesaplar 
            SET mevcut_borc = mevcut_borc + NEW.tutar,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = NEW.hesap_id;
        ELSEIF NEW.hareket_tipi = 'odeme' THEN
            UPDATE acik_hesaplar 
            SET mevcut_borc = mevcut_borc - NEW.tutar,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = NEW.hesap_id;
        END IF;
    ELSEIF NEW.hesap_tipi = 'veresiye' THEN
        IF NEW.hareket_tipi = 'borc' THEN
            UPDATE veresiye_musteriler 
            SET mevcut_borc = mevcut_borc + NEW.tutar,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = NEW.hesap_id;
        ELSEIF NEW.hareket_tipi = 'odeme' THEN
            UPDATE veresiye_musteriler 
            SET mevcut_borc = mevcut_borc - NEW.tutar,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = NEW.hesap_id;
        END IF;
    END IF;
END//
DELIMITER ;

-- Trigger: Hesap ödemelerinde otomatik hareket kaydı
DELIMITER //
CREATE TRIGGER tr_hesap_odeme_after_insert
AFTER INSERT ON hesap_odemeleri
FOR EACH ROW
BEGIN
    INSERT INTO hesap_hareketleri (
        hesap_tipi, hesap_id, hareket_tipi, tutar, 
        aciklama, tarih, saat, kullanici_id
    ) VALUES (
        NEW.hesap_tipi, NEW.hesap_id, 'odeme', NEW.odeme_tutari,
        CONCAT('Ödeme - ', NEW.odeme_yontemi, COALESCE(CONCAT(' - ', NEW.aciklama), '')),
        NEW.odeme_tarihi, NEW.odeme_saati, NEW.kullanici_id
    );
END//
DELIMITER ;