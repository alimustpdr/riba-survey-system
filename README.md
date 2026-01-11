# RİBA Anket Yönetim Sistemi

RİBA (Rights of the Child Impact Assessment - Çocuk Hakları Etki Değerlendirmesi) anket yönetim sistemi, okulların standart RİBA anketlerini dijital ortamda uygulamasını sağlayan bir SaaS platformudur.

## Özellikler

- ✅ **SaaS Yapısı**: Çoklu okul desteği (Super Admin + Okul Yöneticileri)
- ✅ **11 Standart Form**: Okul Öncesi, İlkokul, Ortaokul ve Lise için Öğrenci/Veli/Öğretmen formları
- ✅ **Sınıf Yönetimi**: Her okul kendi sınıflarını tanımlayabilir
- ✅ **Esnek Anket Oluşturma**: Kademe/Grup seçimi, sınıf bazlı hedefleme
- ✅ **Herkese Açık Linkler**: Token tabanlı, sınırsız katılım
- ✅ **Opsiyonel Cinsiyet Alanı**: Okul bazında açılıp kapatılabilir
- ✅ **Gerçek Zamanlı İstatistikler**: Anket bazlı katılım sayıları
- ✅ **Güvenli**: PDO prepared statements, XSS koruması, CSRF token'ları

## Sistem Gereksinimleri

- **PHP**: 7.4 veya üzeri (8.1+ önerilir)
- **MySQL**: 5.7 veya üzeri / MariaDB 10.2+
- **Web Server**: Apache veya Nginx
- **PHP Eklentileri**:
  - PDO
  - PDO_MySQL
  - JSON
  - mbstring
  - session

## CyberPanel'de Kurulum

### 1. Veritabanı Oluşturma

CyberPanel'den MySQL veritabanı oluşturun:

1. CyberPanel > Databases > Create Database
2. Veritabanı adı, kullanıcı adı ve şifre belirleyin
3. Bu bilgileri not alın

### 2. Dosyaları Yükleme

```bash
# SSH ile bağlanın
cd /home/yourdomain.com/public_html

# Git ile klonlayın
git clone https://github.com/alimustpdr/riba-survey-system.git .

# Veya ZIP dosyasını yükleyip çıkartın
```

### 3. Dizin İzinleri

```bash
# Storage ve config dizinlerine yazma izni verin
chmod -R 755 storage
chmod -R 755 config

# Güvenlik için .git dizinini gizleyin
chmod 700 .git
```

### 4. Kurulum Sihirbazını Çalıştırma

1. Tarayıcınızda `https://yourdomain.com/install.php` adresini açın
2. Veritabanı bilgilerini girin:
   - **Host**: localhost (genelde)
   - **Veritabanı Adı**: CyberPanel'de oluşturduğunuz veritabanı
   - **Kullanıcı Adı**: Veritabanı kullanıcısı
   - **Şifre**: Veritabanı şifresi
   - **Veritabanı Zaten Mevcut**: CyberPanel'de oluşturduysanız işaretleyin

3. Süper Admin hesabı oluşturun:
   - Ad Soyad
   - Email
   - Şifre (en az 6 karakter)

4. "Kurulumu Başlat" butonuna tıklayın

### 5. Güvenlik

Kurulum tamamlandıktan sonra:

```bash
# install.php dosyasını silin veya erişimi engelleyin
rm install.php

# Veya .htaccess ile engelleyin
echo "deny from all" > install.php
```

## Kullanım

### Super Admin Paneli

Super Admin aşağıdaki işlemleri yapabilir:

1. **Okul Oluşturma**: `/admin/schools.php`
   - Okul adı ve slug belirleme
   - Her okul için bir yönetici kullanıcı oluşturma
   - Okul bazında cinsiyet alanı aktif/pasif

2. **Sistem Ayarları**: `/admin/settings.php`
   - Global cinsiyet alanı ayarı
   - Uygulama genel ayarları

### Okul Yöneticisi Paneli

Okul yöneticileri aşağıdaki işlemleri yapabilir:

1. **Sınıf Yönetimi**: `/school/classes.php`
   - Sınıf ekleme (örn: 9/A, 10/B)
   - Kademe belirleme (Okul Öncesi, İlkokul, Ortaokul, Lise)
   - Sınıf düzenleme/silme

2. **Anket Oluşturma**: `/school/survey_create.php`
   - 11 standart formdan birini seçme
   - Kademe ve hedef kitle otomatik belirlenir
   - Sınıf seçimi (tek sınıf, birden fazla sınıf veya tüm sınıflar)
   - Benzersiz token oluşturma

3. **Anket Yönetimi**: `/school/surveys.php`
   - Oluşturulan anketleri görüntüleme
   - Anket linkini kopyalama
   - Katılım istatistiklerini görme
   - Anket kapatma/silme

### Anket Doldurma

Katılımcılar token ile anket doldurur:

1. Okul yöneticisi anket linkini paylaşır: `https://yourdomain.com/survey/fill.php?token=xxxxx`
2. Katılımcı:
   - Sınıf seçer (opsiyonel)
   - Cinsiyet bilgisi girer (opsiyonel, aktifse)
   - Tüm soruları A veya B olarak cevaplayar
   - Anketi gönderir
3. Teşekkür sayfası gösterilir

## RİBA Formları

Sistemde 11 standart RİBA formu bulunur:

### Okul Öncesi (2 form)
- Veli Formu (13 madde)
- Öğretmen Formu (13 madde)

### İlkokul (3 form)
- Öğrenci Formu (15 madde)
- Veli Formu (13 madde)
- Öğretmen Formu (16 madde)

### Ortaokul (3 form)
- Öğrenci Formu (18 madde)
- Veli Formu (16 madde)
- Öğretmen Formu (18 madde)

### Lise (3 form)
- Öğrenci Formu (20 madde)
- Veli Formu (19 madde)
- Öğretmen Formu (19 madde)

Her formda A/B şeklinde iki seçenek bulunur.

## Güvenlik Notları

- ✅ SQL injection'a karşı PDO prepared statements kullanılır
- ✅ XSS'e karşı tüm çıktılar `htmlspecialchars()` ile korunur
- ✅ CSRF token'ları ile form güvenliği sağlanır
- ✅ Şifreler `password_hash()` ile hashlenir
- ✅ Session yönetimi güvenli şekilde yapılır
- ✅ `install.php` kurulum sonrası silinmeli veya engellenmelidir

## Dosya Yapısı

```
riba-survey-system/
├── admin/                  # Super Admin paneli
│   ├── index.php          # Dashboard
│   ├── schools.php        # Okul yönetimi
│   └── settings.php       # Sistem ayarları
├── school/                # Okul yöneticisi paneli
│   ├── index.php          # Dashboard
│   ├── classes.php        # Sınıf yönetimi
│   ├── surveys.php        # Anket listesi
│   └── survey_create.php  # Yeni anket oluştur
├── survey/                # Public anket arayüzü
│   └── fill.php          # Anket doldurma
├── includes/              # Ortak dosyalar
│   ├── auth.php          # Kimlik doğrulama
│   ├── db.php            # Veritabanı bağlantısı
│   ├── functions.php     # Yardımcı fonksiyonlar
│   └── layout.php        # Layout bileşenleri
├── config/                # Konfigürasyon (kurulumda oluşur)
│   ├── config.php        # DB ayarları
│   └── .installed        # Kurulum flag
├── database/              # Veritabanı dosyaları
│   ├── schema.sql        # Tablo yapısı
│   └── forms_data.sql    # Form verileri
├── storage/               # Log ve cache (yazılabilir olmalı)
│   └── logs/
├── install.php            # Kurulum sihirbazı
├── login.php              # Giriş sayfası
├── logout.php             # Çıkış
├── index.php              # Ana sayfa (yönlendirme)
└── README.md              # Bu dosya
```

## Sorun Giderme

### Kurulum Hataları

**"Veritabanı bağlantısı başarısız"**
- Veritabanı bilgilerini kontrol edin
- MySQL'in çalıştığından emin olun
- Kullanıcının veritabanına erişim yetkisi olduğunu kontrol edin

**"CREATE DATABASE yetkisi yok"**
- Kurulum formunda "Veritabanı zaten mevcut" seçeneğini işaretleyin
- CyberPanel'den veritabanını önceden oluşturun

**"config dizinine yazılamıyor"**
```bash
chmod -R 755 config
chown -R apache:apache config  # veya nginx:nginx
```

### Çalışma Hataları

**"Oturum açılamıyor"**
- PHP session ayarlarını kontrol edin
- `storage` dizininin yazılabilir olduğundan emin olun

**"Anket görünmüyor"**
- Anketin "aktif" durumda olduğunu kontrol edin
- Token'ın doğru olduğundan emin olun

## Destek ve İletişim

Sorunlar için GitHub Issues kullanabilirsiniz:
https://github.com/alimustpdr/riba-survey-system/issues

## Lisans

Bu proje MIT lisansı altında lisanslanmıştır.

## Katkıda Bulunanlar

- [@alimustpdr](https://github.com/alimustpdr)

## Değişiklik Geçmişi

### v1.0.0 (MVP)
- ✅ Temel sistem altyapısı
- ✅ Super Admin ve Okul Yöneticisi panelleri
- ✅ 11 standart RİBA formu
- ✅ Anket oluşturma ve doldurma
- ✅ Sınıf yönetimi
- ✅ Opsiyonel cinsiyet alanı
- ✅ Token tabanlı anket paylaşımı
- ✅ Güvenlik özellikleri

### Gelecek Sürümler
- [ ] Ödeme entegrasyonu (İyzico)
- [ ] %30 katılım kuralı kontrolü
- [ ] Detaylı raporlama ve analiz
- [ ] Excel/PDF export
- [ ] Email bildirimleri
- [ ] Çoklu dil desteği
