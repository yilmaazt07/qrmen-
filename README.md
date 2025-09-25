# 🍽️ Tiryakideyim QR Menü Sistemi

**Geliştirici:** Mustafa Yılmaz Aydilek
**Proje Türü:** Web Tabanlı QR Menü ve Yönetim Sistemi  
**Teknolojiler:** PHP, MySQL, HTML5, CSS3, JavaScript  

## 📋 Proje Hakkında

Tiryakideyim QR Menü Sistemi, kafe ve restoran işletmeleri için geliştirilmiş modern bir dijital menü çözümüdür. Müşteriler QR kod okutarak menüye erişebilir, işletme sahipleri ise kapsamlı yönetim paneli ile menülerini kolayca yönetebilir.

## ✨ Özellikler

### 🎯 Müşteri Tarafı
- **QR Kod ile Erişim**: Kolay ve temassız menü erişimi
- **Responsive Tasarım**: Mobil, tablet ve masaüstü uyumlu
- **Kategori Bazlı Menü**: Düzenli ve kullanıcı dostu arayüz
- **Şefin Spesiyalleri**: Öne çıkan ürünler bölümü
- **Görsel Zengin İçerik**: Ürün görselleri ve detaylı açıklamalar
- **Alerji Uyarıları**: Müşteri güvenliği için alerjen bilgileri

### 🛠️ Yönetim Paneli
- **Dashboard**: Genel istatistikler ve özet bilgiler
- **Ürün Yönetimi**: Ürün ekleme, düzenleme, silme
- **Kategori Yönetimi**: Hiyerarşik kategori yapısı
- **Adisyon Sistemi**: Sipariş alma ve yönetim
- **Hesap Yönetimi**: Açık hesap ve veresiye sistemi
- **Raporlama**: Detaylı satış ve hesap raporları
- **Tema Yönetimi**: Görsel özelleştirme seçenekleri
- **Ayarlar**: Sistem konfigürasyonu

### 💳 Hesap Yönetimi Sistemi
- **Açık Hesap**: Kurumsal müşteriler için kredi limiti sistemi
- **Veresiye**: Bireysel müşteriler için borç takibi
- **Ödeme Takibi**: Detaylı ödeme geçmişi
- **Borç Raporları**: Kapsamlı finansal raporlama

## 🚀 Kurulum

### Gereksinimler
- PHP 7.4 veya üzeri
- MySQL 5.7 veya üzeri
- Apache/Nginx web sunucusu
- XAMPP/WAMP (yerel geliştirme için)

### Kurulum Adımları

1. **Veritabanını Oluşturun**
   - MySQL'de yeni bir veritabanı oluşturun
   - `sql/tablo.sql` dosyasını çalıştırın
   - `sql/yilmaz.sql` dosyasını çalıştırın (hesap yönetimi için)

2. **Veritabanı Bağlantısını Yapılandırın**
   - `baglan.php` dosyasındaki veritabanı bilgilerini güncelleyin
   - Yönetim paneli dosyalarındaki bağlantı bilgilerini güncelleyin

3. **Web Sunucusunu Başlatın**
   - Dosyaları web sunucunuzun root dizinine kopyalayın
   - Tarayıcıdan projeye erişin

## 📁 Proje Yapısı

```
qrmenü/
├── baglan.php              # Veritabanı bağlantı dosyası
├── qrmenu.php              # Ana menü sayfası
├── README.md               # Bu dosya
├── sql/                    # Veritabanı dosyaları
│   ├── tablo.sql          # Ana tablolar
│   ├── yilmaz.sql         # Hesap yönetimi tabloları
│   ├── tema.sql           # Tema tabloları
│   └── rapor.sql          # Rapor tabloları
├── uploads/               # Yüklenen dosyalar
└── yönetim/              # Yönetim paneli
    ├── panel.php         # Ana dashboard
    ├── urunler.php       # Ürün yönetimi
    ├── kategoriler.php   # Kategori yönetimi
    ├── adisyon.php       # Sipariş yönetimi
    ├── hesap.php         # Hesap yönetimi
    ├── raporlar.php      # Raporlama
    ├── ayarlar.php       # Sistem ayarları
    └── giris.php         # Giriş sayfası
```

## 🔐 Varsayılan Giriş Bilgileri

**Yönetici Paneli:**
- Kullanıcı Adı: `admin`
- Şifre: `123456`

> ⚠️ **Güvenlik Uyarısı:** İlk kurulumdan sonra mutlaka şifrenizi değiştirin!

## 🎨 Özelleştirme

### Tema Değiştirme
- Yönetim panelinden "Ayarlar" bölümüne gidin
- Renk şeması, font ve görsel ayarları yapın
- Değişiklikler anında uygulanır

### Logo ve Görsel Ekleme
- `uploads/` klasörüne görsellerinizi yükleyin
- Yönetim panelinden ürün görsellerini atayın

## 📱 Kullanım

### Müşteri Kullanımı
1. QR kodu okutun veya doğrudan link ile erişin
2. Kategoriler arasında gezinin
3. Ürün detaylarını inceleyin
4. Sipariş vermek için personeli çağırın

### İşletme Kullanımı
1. Yönetim paneline giriş yapın
2. Ürün ve kategorilerinizi ekleyin
3. Fiyatları güncelleyin
4. Siparişleri takip edin
5. Raporları inceleyin

## 🔧 Teknik Detaylar

### Veritabanı Yapısı
- **Admins**: Yönetici kullanıcıları
- **Kategoriler**: Ürün kategorileri (hiyerarşik)
- **Urunler**: Menü ürünleri
- **Ayarlar**: Sistem ayarları
- **Temalar**: Görsel tema ayarları
- **acik_hesaplar**: Açık hesap sistemi
- **veresiye_musteriler**: Veresiye müşteri sistemi
- **hesap_islemleri**: Hesap işlem geçmişi

### Güvenlik Özellikleri
- Session tabanlı kimlik doğrulama
- SQL injection koruması (PDO)
- XSS koruması
- CSRF token koruması

## 🤝 Katkıda Bulunma

1. Bu repository'yi fork edin
2. Feature branch oluşturun (`git checkout -b feature/AmazingFeature`)
3. Değişikliklerinizi commit edin (`git commit -m 'Add some AmazingFeature'`)
4. Branch'inizi push edin (`git push origin feature/AmazingFeature`)
5. Pull Request oluşturun

## 📄 Lisans

Bu proje MIT lisansı altında lisanslanmıştır. Detaylar için `LICENSE` dosyasına bakın.

## 📞 İletişim

**Mustafa Yılmaz Aydilek**
- GitHub: [@yilmaazt07](https://github.com/yilmaazt07)
- Email: [yilmaz@ymcotomasyon.com.tr](mailto:yilmaz@ymcotomasyon.com.tr)

## 🙏 Teşekkürler

Bu projeyi kullandığınız için teşekkür ederiz! Geri bildirimleriniz ve katkılarınız bizim için çok değerli.

---

⭐ **Bu projeyi beğendiyseniz yıldız vermeyi unutmayın!**
