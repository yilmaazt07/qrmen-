-- Satış Raporları için Veritabanı Tabloları
-- Oluşturulma Tarihi: 2024

-- Satış işlemlerini kaydetmek için ana tablo
CREATE TABLE IF NOT EXISTS satislar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tarih DATE NOT NULL,
    saat TIME NOT NULL,
    toplam_tutar DECIMAL(10,2) NOT NULL,
    urun_sayisi INT NOT NULL,
    adisyon_no VARCHAR(50),
    kullanici_id INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Satış detaylarını kaydetmek için tablo (hangi ürünlerden kaç adet satıldı)
CREATE TABLE IF NOT EXISTS satis_detaylari (
    id INT AUTO_INCREMENT PRIMARY KEY,
    satis_id INT NOT NULL,
    urun_id INT NOT NULL,
    urun_adi VARCHAR(255) NOT NULL,
    miktar INT NOT NULL,
    birim_fiyat DECIMAL(10,2) NOT NULL,
    toplam_fiyat DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (satis_id) REFERENCES satislar(id) ON DELETE CASCADE
);

-- Günlük satış özetleri için tablo (performans için)
CREATE TABLE IF NOT EXISTS gunluk_ozet (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tarih DATE NOT NULL UNIQUE,
    toplam_satis DECIMAL(10,2) DEFAULT 0,
    toplam_adisyon INT DEFAULT 0,
    toplam_urun INT DEFAULT 0,
    en_cok_satan_urun VARCHAR(255),
    en_cok_satan_miktar INT DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Saatlik satış verileri için tablo (grafik için)
CREATE TABLE IF NOT EXISTS saatlik_satis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tarih DATE NOT NULL,
    saat INT NOT NULL, -- 0-23 arası saat
    toplam_tutar DECIMAL(10,2) DEFAULT 0,
    adisyon_sayisi INT DEFAULT 0,
    UNIQUE KEY unique_tarih_saat (tarih, saat)
);

-- En çok satan ürünler için view
CREATE OR REPLACE VIEW en_cok_satan_urunler AS
SELECT 
    urun_adi,
    SUM(miktar) as toplam_miktar,
    SUM(toplam_fiyat) as toplam_gelir,
    COUNT(DISTINCT satis_id) as kac_adisyonda
FROM satis_detaylari sd
JOIN satislar s ON sd.satis_id = s.id
WHERE s.tarih >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
GROUP BY urun_adi
ORDER BY toplam_miktar DESC;

-- Günlük satış trendi için view
CREATE OR REPLACE VIEW gunluk_trend AS
SELECT 
    tarih,
    COUNT(*) as adisyon_sayisi,
    SUM(toplam_tutar) as gunluk_ciro,
    AVG(toplam_tutar) as ortalama_adisyon,
    SUM(urun_sayisi) as toplam_urun
FROM satislar
WHERE tarih >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
GROUP BY tarih
ORDER BY tarih DESC;

-- Saatlik satış trendi için view
CREATE OR REPLACE VIEW saatlik_trend AS
SELECT 
    saat,
    SUM(toplam_tutar) as saatlik_ciro,
    SUM(adisyon_sayisi) as saatlik_adisyon,
    AVG(toplam_tutar) as ortalama_tutar
FROM saatlik_satis
WHERE tarih >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
GROUP BY saat
ORDER BY saat;

-- Örnek veri ekleme (test için)
INSERT IGNORE INTO satislar (tarih, saat, toplam_tutar, urun_sayisi, adisyon_no) VALUES
(CURDATE(), '09:30:00', 45.50, 3, 'ADI001'),
(CURDATE(), '10:15:00', 28.75, 2, 'ADI002'),
(CURDATE(), '11:45:00', 67.25, 4, 'ADI003'),
(DATE_SUB(CURDATE(), INTERVAL 1 DAY), '14:20:00', 52.00, 3, 'ADI004'),
(DATE_SUB(CURDATE(), INTERVAL 1 DAY), '16:30:00', 38.50, 2, 'ADI005');

-- Trigger: Satış eklendiğinde günlük özeti güncelle
DELIMITER //
CREATE TRIGGER IF NOT EXISTS update_gunluk_ozet
AFTER INSERT ON satislar
FOR EACH ROW
BEGIN
    INSERT INTO gunluk_ozet (tarih, toplam_satis, toplam_adisyon, toplam_urun)
    VALUES (NEW.tarih, NEW.toplam_tutar, 1, NEW.urun_sayisi)
    ON DUPLICATE KEY UPDATE
        toplam_satis = toplam_satis + NEW.toplam_tutar,
        toplam_adisyon = toplam_adisyon + 1,
        toplam_urun = toplam_urun + NEW.urun_sayisi;
        
    -- Saatlik veriyi de güncelle
    INSERT INTO saatlik_satis (tarih, saat, toplam_tutar, adisyon_sayisi)
    VALUES (NEW.tarih, HOUR(NEW.saat), NEW.toplam_tutar, 1)
    ON DUPLICATE KEY UPDATE
        toplam_tutar = toplam_tutar + NEW.toplam_tutar,
        adisyon_sayisi = adisyon_sayisi + 1;
END//
DELIMITER ;

-- İndeksler (performans için)
CREATE INDEX idx_satislar_tarih ON satislar(tarih);
CREATE INDEX idx_satislar_saat ON satislar(saat);
CREATE INDEX idx_satis_detaylari_urun ON satis_detaylari(urun_adi);
CREATE INDEX idx_saatlik_tarih_saat ON saatlik_satis(tarih, saat);

-- Başarılı kurulum mesajı
SELECT 'Satış raporları veritabanı tabloları başarıyla oluşturuldu!' as mesaj;