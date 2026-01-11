# RİBA Anket Yönetim Sistemi

RİBA (Rights in Balance Assessment) Anket Yönetim Sistemi, okullar için geliştirilmiş çok kiracılı bir anket platformudur. Sistem, farklı kademe ve rollerde standartlaştırılmış anket formları kullanarak haklar bilinci değerlendirmesi yapmayı sağlar.

## Özellikler

### Çok Kiracılı (Multi-tenant) Yapı
- **Süper Admin**: Tüm okulları görüntüler ve yönetir
- **Okul Yöneticisi**: Sadece kendi okulunun anketlerini yönetir

### Anket Yönetimi
- 11 standart RİBA form şablonu (4 kademe x 3 rol)
- Kademe: Okul Öncesi, İlkokul, Ortaokul, Lise
- Roller: Öğrenci, Veli, Öğretmen
- Her form 10 sabit soru içerir (A/B seçenekli)
- Sınıf bazlı veya "tüm sınıflar" hedefleme
- Token tabanlı paylaşılabilir linkler
- Sınırsız katılım

### Güvenlik
- PDO prepared statements (SQL injection koruması)
- CSRF token koruması
- XSS koruması (output escaping)
- Güvenli şifre saklama (bcrypt)
- Oturum güvenliği

### Özelleştirme
- Opsiyonel cinsiyet alanı (sistem ve okul bazında ayarlanabilir)
- Anket başlık ve açıklama özelleştirme

## CyberPanel Kurulum Adımları

### 1. Veritabanı Oluşturma

CyberPanel üzerinden veritabanınızı oluşturun:

1. CyberPanel'e giriş yapın
2. **Databases** > **Create Database** bölümüne gidin
3. Veritabanı adını girin (örn: `riba_system`)
4. MySQL kullanıcısı oluşturun ve güçlü bir şifre belirleyin
5. Kullanıcıya veritabanı üzerinde tam yetki verin
6. Veritabanı bilgilerinizi not edin:
   - Host: `localhost`
   - Veritabanı Adı: `riba_system`
   - Kullanıcı Adı: `riba_user`
   - Şifre: `********`

### 2. Dosyaları Yükleme

1. Projeyi GitHub'dan indirin veya klonlayın
2. Dosyaları CyberPanel üzerinden domain'inizin `public_html` dizinine yükleyin
3. FileManager veya FTP kullanarak yükleme yapabilirsiniz

### 3. Dizin İzinleri

Aşağıdaki dizinlerin yazılabilir olduğundan emin olun:

```bash
chmod 755 /home/[kullanıcı]/public_html
chmod 755 /home/[kullanıcı]/public_html/config
chmod 644 /home/[kullanıcı]/public_html/config/config.php  # Oluşturulduktan sonra
```

### 4. Kurulum Sihirbazını Çalıştırma

1. Tarayıcınızda `http://yourdomain.com/install.php` adresine gidin
2. Formu doldurun:
   - **Veritabanı Host**: localhost
   - **Veritabanı Adı**: CyberPanel'den oluşturduğunuz DB adı
   - **MySQL Kullanıcı Adı**: Oluşturduğunuz kullanıcı adı
   - **MySQL Şifre**: Belirlediğiniz şifre
   - **"Veritabanı zaten oluşturuldu"** seçeneğini işaretleyin
   - **Süper Admin Bilgileri**: Ad, e-posta ve şifre girin
3. **Kurulumu Başlat** butonuna tıklayın
4. Kurulum tamamlandıktan sonra `login.php` sayfasına yönlendirileceksiniz

### 5. İlk Giriş

1. Kurulumda belirlediğiniz e-posta ve şifre ile giriş yapın
2. Süper Admin olarak sisteme erişim sağlayacaksınız

## Kullanım Kılavuzu

### Süper Admin İşlemleri

1. **Okul Ekleme**:
   - Okullar menüsünden "Yeni Okul Ekle"
   - Okul adı ve slug (benzersiz) girin
   - Durumu belirleyin (aktif/pasif)

2. **Okul Yöneticisi Oluşturma**:
   - Okul Yöneticileri menüsünden "Yeni Yönetici Ekle"
   - Ad, e-posta, şifre ve okul seçin
   - Yönetici oluşturulacak

3. **Sistem Ayarları**:
   - Ayarlar menüsünden cinsiyet alanı gösterimini açıp kapatabilirsiniz

### Okul Yöneticisi İşlemleri

1. **Sınıf Ekleme**:
   - Sınıflar menüsünden sınıflarınızı ekleyin
   - Kademe, ad ve öğrenci sayısını girin

2. **Anket Oluşturma**:
   - "Yeni Anket" menüsüne gidin
   - Başlık ve açıklama girin
   - 11 form şablonundan birini seçin (kademe + rol)
   - Hedef kitleyи seçin (tüm sınıflar veya belirli sınıflar)
   - Cinsiyet alanı gösterimini belirleyin
   - Anketi oluşturun

3. **Anket Paylaşma**:
   - Anketler listesinden ilgili anketi bulun
   - "Link" butonuna tıklayarak benzersiz linki kopyalayın
   - Linki katılımcılarla paylaşın (e-posta, WhatsApp, vb.)

### Katılımcı (Anket Doldurma)

1. Paylaşılan linke tıklayın
2. Anketi doldurun:
   - (Opsiyonel) Cinsiyet seçin
   - Her soru için A veya B seçeneğini işaretleyin
3. "Anketi Gönder" butonuna tıklayın
4. Teşekkür mesajını görün

## Form Şablonları

Sistemde aşağıdaki 11 standart form bulunmaktadır:

1. Okul Öncesi - Öğrenci
2. Okul Öncesi - Veli
3. Okul Öncesi - Öğretmen
4. İlkokul - Öğrenci
5. İlkokul - Veli
6. İlkokul - Öğretmen
7. Ortaokul - Öğrenci
8. Ortaokul - Veli
9. Ortaokul - Öğretmen
10. Lise - Öğrenci
11. Lise - Veli
12. Lise - Öğretmen

Her form 10 soru içerir ve sorular A/B seçeneklidir. Katılımcılar her soruda kendileri için daha önemli olan seçeneği işaretler.

## Önemli Notlar

### Güvenlik
- Kurulumdan sonra `install.php` dosyası tekrar çalıştırılamaz
- `config/` dizini .htaccess ile korunmuştur
- Tüm formlar CSRF token ile korunmuştur
- Şifreler bcrypt ile hash'lenerek saklanır

### Yedekleme
- Düzenli olarak veritabanınızı yedekleyin
- CyberPanel'den otomatik yedekleme ayarlayabilirsiniz

### Sorun Giderme

**Kurulum sırasında "Permission denied" hatası**:
- Dizin izinlerini kontrol edin
- `config/` dizininin yazılabilir olduğundan emin olun

**"Veritabanı bağlantı hatası"**:
- Veritabanı bilgilerini kontrol edin
- MySQL servisinin çalıştığından emin olun
- Kullanıcının veritabanına erişim yetkisi olduğunu doğrulayın

**"Foreign key constraint" veya "errno: 150" hatası**:
- Bu hata, veritabanı şeması yüklenirken tablo sıralama sorununu gösterir
- Çözüm adımları:
  1. CyberPanel'den veritabanını tamamen silin
  2. Aynı isimde yeni bir veritabanı oluşturun
  3. Kurulum sihirbazını tekrar çalıştırın
- Not: Bu sorun güncel sürümde düzeltilmiştir

**Kurulum yarıda kaldı / Bazı tablolar oluşmuş**:
- Veritabanını CyberPanel'den tamamen silin
- Yeni bir veritabanı oluşturun
- Kurulumu baştan çalıştırın
- Yarım kalmış kurulum üzerine devam etmeyin

**"Session hatası"**:
- PHP session ayarlarını kontrol edin
- Geçici dizin izinlerini kontrol edin

## Teknik Gereksinimler

- PHP 7.4 veya üzeri
- MySQL 5.7 veya üzeri
- PDO PHP Extension
- JSON PHP Extension
- mbstring PHP Extension

## Lisans

Bu proje özel kullanım içindir.

## Destek

Sorunlarınız için GitHub Issues bölümünü kullanabilirsiniz.
