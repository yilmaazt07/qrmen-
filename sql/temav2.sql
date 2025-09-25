-- QR Menü Tema Koleksiyonu v2
-- Mevsimsel ve Özel Atmosfer Temaları
-- NOT: Ayarlar tablosunda ayar_adi UNIQUE olduğu için her tema ayrı ayar_adi ile ekleniyor

-- Mevsimsel Temalar

-- YAZ TEMALARı
INSERT INTO Ayarlar (ayar_adi, ayar_degeri, aciklama) VALUES 
('tema_summer_breeze', 'summer-breeze', 'Yaz Esintisi - Açık mavi ve beyaz tonlar, serinletici atmosfer'),
('tema_tropical_paradise', 'tropical-paradise', 'Tropik Cennet - Palmiye yeşili ve okyanus mavisi'),
('tema_sunny_terrace', 'sunny-terrace', 'Güneşli Teras - Sarı ve turuncu tonlar, enerjik yaz havası'),
('tema_beach_cafe', 'beach-cafe', 'Sahil Kafesi - Kum beji ve deniz mavisi, tatil atmosferi'),
('tema_summer_garden', 'summer-garden', 'Yaz Bahçesi - Çiçek renkleri ve taze yeşillikler'),
('tema_ice_cool', 'ice-cool', 'Buz Serinliği - Buzlu içecek teması, soğuk tonlar'),
('tema_sunset_glow', 'sunset-glow', 'Gün Batımı - Pembe, turuncu ve mor geçişler');

-- KIŞ TEMALARı
INSERT INTO Ayarlar (ayar_adi, ayar_degeri, aciklama) VALUES 
('tema_winter_wonderland', 'winter-wonderland', 'Kış Masalı - Kar beyazı ve gümüş tonlar'),
('tema_cozy_fireplace', 'cozy-fireplace', 'Sıcak Şömine - Kahverengi ve kırmızı tonlar, sıcak atmosfer'),
('tema_snowy_cabin', 'snowy-cabin', 'Karlı Kulübe - Ahşap tonları ve kar beyazı'),
('tema_hot_chocolate', 'hot-chocolate', 'Sıcak Çikolata - Koyu kahve ve krem renkleri'),
('tema_winter_forest', 'winter-forest', 'Kış Ormanı - Çam yeşili ve kar beyazı'),
('tema_arctic_blue', 'arctic-blue', 'Arktik Mavisi - Buzul mavisi ve beyaz tonlar'),
('tema_warm_wool', 'warm-wool', 'Sıcak Yün - Bej ve kahverengi sıcak tonlar');

-- SONBAHAR TEMALARı
INSERT INTO Ayarlar (ayar_adi, ayar_degeri, aciklama) VALUES 
('tema_autumn_leaves', 'autumn-leaves', 'Sonbahar Yaprakları - Altın sarısı, turuncu ve kırmızı'),
('tema_harvest_time', 'harvest-time', 'Hasat Zamanı - Toprak tonları ve altın renkleri'),
('tema_pumpkin_spice', 'pumpkin-spice', 'Balkabağı Baharatı - Turuncu ve kahverengi tonlar'),
('tema_golden_october', 'golden-october', 'Altın Ekim - Sarı ve kahverengi geçişler'),
('tema_maple_syrup', 'maple-syrup', 'Akçaağaç Şurubu - Amber ve bal renkleri'),
('tema_rustic_autumn', 'rustic-autumn', 'Rustik Sonbahar - Doğal ahşap ve yaprak tonları'),
('tema_cinnamon_warmth', 'cinnamon-warmth', 'Tarçın Sıcaklığı - Baharat renkleri ve sıcak tonlar');

-- İLKBAHAR TEMALARı
INSERT INTO Ayarlar (ayar_adi, ayar_degeri, aciklama) VALUES 
('tema_spring_bloom', 'spring-bloom', 'İlkbahar Çiçekleri - Pastel pembe ve yeşil tonlar'),
('tema_fresh_mint', 'fresh-mint', 'Taze Nane - Açık yeşil ve beyaz, ferahlatıcı'),
('tema_cherry_blossom', 'cherry-blossom', 'Kiraz Çiçeği - Pembe ve beyaz, Japon tarzı'),
('tema_morning_dew', 'morning-dew', 'Sabah Çiyi - Açık mavi ve yeşil, taze atmosfer'),
('tema_tulip_garden', 'tulip-garden', 'Lale Bahçesi - Renkli çiçek tonları'),
('tema_spring_rain', 'spring-rain', 'İlkbahar Yağmuru - Gri ve yeşil tonlar, sakin atmosfer'),
('tema_butterfly_meadow', 'butterfly-meadow', 'Kelebek Çayırı - Çiçek renkleri ve doğa tonları');

-- Özel Atmosfer Temaları

-- GECE YARISI TEMALARı
INSERT INTO Ayarlar (ayar_adi, ayar_degeri, aciklama) VALUES 
('tema_midnight_blue', 'midnight-blue', 'Gece Yarısı Mavisi - Koyu mavi ve gümüş tonlar'),
('tema_neon_nights', 'neon-nights', 'Neon Geceler - Parlak neon renkler, şehir gecesi'),
('tema_starry_sky', 'starry-sky', 'Yıldızlı Gökyüzü - Koyu mavi ve altın yıldızlar'),
('tema_moonlight_cafe', 'moonlight-cafe', 'Ay Işığı Kafesi - Gümüş ve koyu mavi tonlar'),
('tema_city_lights', 'city-lights', 'Şehir Işıkları - Koyu tonlar ve parlak vurgular'),
('tema_dark_elegance', 'dark-elegance', 'Koyu Zarafet - Siyah ve altın, lüks atmosfer'),
('tema_night_owl', 'night-owl', 'Gece Kuşu - Koyu mor ve gümüş tonlar');

-- ROMANTİK TEMALARı
INSERT INTO Ayarlar (ayar_adi, ayar_degeri, aciklama) VALUES 
('tema_rose_garden', 'rose-garden', 'Gül Bahçesi - Pembe ve kırmızı tonlar, romantik'),
('tema_candlelight', 'candlelight', 'Mum Işığı - Altın sarısı ve sıcak tonlar'),
('tema_valentine_love', 'valentine-love', 'Sevgililer Günü - Kırmızı ve pembe, aşk teması'),
('tema_paris_romance', 'paris-romance', 'Paris Romantizmi - Pastel tonlar ve zarif desenler'),
('tema_wine_cellar', 'wine-cellar', 'Şarap Mahzeni - Bordo ve koyu kırmızı tonlar'),
('tema_sunset_romance', 'sunset-romance', 'Romantik Gün Batımı - Pembe ve turuncu geçişler'),
('tema_vintage_love', 'vintage-love', 'Vintage Aşk - Nostaljik pembe ve krem tonları');

-- MOTORCU TEMALARı
INSERT INTO Ayarlar (ayar_adi, ayar_degeri, aciklama) VALUES 
('tema_biker_garage', 'biker-garage', 'Motorcu Garajı - Siyah ve krom, endüstriyel tasarım'),
('tema_route_66', 'route-66', 'Route 66 - Kırmızı, beyaz ve mavi, Amerikan tarzı'),
('tema_harley_style', 'harley-style', 'Harley Tarzı - Turuncu ve siyah, güçlü tasarım'),
('tema_speed_demon', 'speed-demon', 'Hız Şeytanı - Kırmızı ve siyah, yarış teması'),
('tema_chrome_steel', 'chrome-steel', 'Krom Çelik - Metalik gri ve siyah tonlar'),
('tema_rebel_cafe', 'rebel-cafe', 'İsyankâr Kafe - Siyah ve kırmızı, rock teması'),
('tema_vintage_motor', 'vintage-motor', 'Vintage Motor - Kahverengi deri ve metal tonları'),
('tema_thunder_road', 'thunder-road', 'Gök Gürültüsü Yolu - Koyu gri ve sarı vurgular'),
('tema_iron_horse', 'iron-horse', 'Demir At - Siyah ve turuncu, güçlü motorcu ruhu'),
('tema_roadhouse_grill', 'roadhouse-grill', 'Yol Evi - Kahverengi ve kırmızı, biker bar atmosferi');

-- Bonus Temalar
INSERT INTO Ayarlar (ayar_adi, ayar_degeri, aciklama) VALUES 
('tema_cosmic_cafe', 'cosmic-cafe', 'Kozmik Kafe - Mor ve mavi uzay teması'),
('tema_retro_diner', 'retro-diner', 'Retro Lokanta - 50ler tarzı kırmızı ve beyaz'),
('tema_jungle_adventure', 'jungle-adventure', 'Orman Macerası - Yeşil tonlar ve doğa teması'),
('tema_desert_sunset', 'desert-sunset', 'Çöl Gün Batımı - Turuncu ve kahverengi tonlar'),
('tema_ocean_depths', 'ocean-depths', 'Okyanus Derinlikleri - Koyu mavi ve turkuaz');

-- Tema Ayarları Tablosu (Eğer yoksa)
-- CREATE TABLE IF NOT EXISTS Ayarlar (
--     id INT AUTO_INCREMENT PRIMARY KEY,
--     ayar_adi VARCHAR(50) NOT NULL,
--     ayar_degeri VARCHAR(100) NOT NULL,
--     aciklama TEXT,
--     olusturma_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP
-- );

-- Tema seçimi için örnek sorgular:
-- Tüm temaları listele:
-- SELECT * FROM Ayarlar WHERE ayar_adi LIKE 'tema_%' ORDER BY aciklama;
-- Belirli bir temayı seç:
-- SELECT * FROM Ayarlar WHERE ayar_adi = 'tema_summer_breeze';
-- Mevsimsel temaları listele:
-- SELECT * FROM Ayarlar WHERE ayar_adi LIKE 'tema_summer_%' OR ayar_adi LIKE 'tema_winter_%' OR ayar_adi LIKE 'tema_autumn_%' OR ayar_adi LIKE 'tema_spring_%';