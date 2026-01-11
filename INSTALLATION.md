# RİBA Anket Sistemi - Hızlı Kurulum Kılavuzu

Bu kılavuz, RİBA anket sistemini CyberPanel üzerinde kurmanız için adım adım talimatlar içerir.

## Adım 1: Veritabanı Hazırlığı

1. CyberPanel'e giriş yapın
2. **Databases** > **Create Database** menüsüne gidin
3. Yeni veritabanı oluşturun:
   - **Database Name**: örn. `riba_system`
   - **Database Username**: örn. `riba_user`
   - **Password**: Güçlü bir şifre belirleyin
4. **Create Database** butonuna tıklayın
5. Oluşturulan bilgileri bir yere not edin

## Adım 2: Dosyaları Yükleme

### Yöntem 1: Git ile (Önerilen)

SSH ile sunucunuza bağlanın:

```bash
cd /home/yourdomain.com/public_html
git clone https://github.com/alimustpdr/riba-survey-system.git .
```

### Yöntem 2: FTP/File Manager ile

1. Projeyi ZIP olarak indirin
2. CyberPanel File Manager veya FTP ile `public_html` dizinine yükleyin
3. ZIP dosyasını çıkartın

## Adım 3: Dizin İzinleri

SSH ile bağlanın ve izinleri ayarlayın:

```bash
cd /home/yourdomain.com/public_html

# Storage ve config dizinlerine yazma izni
chmod -R 755 storage
chmod -R 755 config

# Güvenlik için .git'i gizle (git kullandıysanız)
chmod 700 .git
```

## Adım 4: Kurulum Sihirbazını Çalıştırma

1. Tarayıcınızda sitenizi açın: `https://yourdomain.com/install.php`

2. **Veritabanı Bilgileri** bölümünü doldurun:
   - **Veritabanı Host**: `localhost`
   - **Veritabanı Adı**: Adım 1'de oluşturduğunuz veritabanı adı
   - **MySQL Kullanıcı Adı**: Adım 1'de oluşturduğunuz kullanıcı adı
   - **MySQL Şifre**: Adım 1'de belirlediğiniz şifre
   - ✅ **"Veritabanı zaten mevcut"** kutucuğunu işaretleyin

3. **Süper Admin Hesabı** bölümünü doldurun:
   - **Ad Soyad**: Örn. "Sistem Yöneticisi"
   - **Email**: Giriş yapmak için kullanacağınız email
   - **Şifre**: Güçlü bir şifre belirleyin (en az 6 karakter)

4. **"Kurulumu Başlat"** butonuna tıklayın

5. Kurulum tamamlanınca otomatik olarak giriş sayfasına yönlendirileceksiniz

## Adım 5: Güvenlik

Kurulum tamamlandıktan sonra `install.php` dosyasını silin:

```bash
cd /home/yourdomain.com/public_html
rm install.php
```

veya SSH erişiminiz yoksa File Manager'dan silin.

## Adım 6: İlk Giriş

1. `https://yourdomain.com/login.php` adresine gidin
2. Süper admin email ve şifrenizle giriş yapın
3. Otomatik olarak admin paneline yönlendirileceksiniz

## Sonraki Adımlar

Sistem kuruldu! Şimdi şunları yapabilirsiniz:

### Super Admin olarak:

1. **Okul Oluşturma**:
   - Admin Panel > Okullar > Yeni Okul Ekle
   - Okul adı, slug ve yönetici bilgilerini girin
   - Cinsiyet alanını aktif/pasif yapın

2. **Sistem Ayarları**:
   - Admin Panel > Ayarlar
   - Global cinsiyet alanı ayarını yapın

### Okul Yöneticisi olarak:

1. Okul yöneticisi hesabıyla giriş yapın
2. **Sınıf Ekleme**:
   - Sınıflar > Yeni Sınıf Ekle
   - Sınıf adı ve kademe seçin (örn. 9/A, Lise)

3. **Anket Oluşturma**:
   - Yeni Anket > Form seçin
   - Hedef sınıfları seçin veya "Tüm Sınıflar" işaretleyin
   - Anket başlığı ve açıklama girin
   - Anketi oluştur

4. **Anket Paylaşma**:
   - Oluşturulan anketin detay sayfasından linki kopyalayın
   - Linki öğrenciler/veliler/öğretmenlerle paylaşın

## Sorun Giderme

### "Veritabanı bağlantısı başarısız"
- Veritabanı bilgilerini kontrol edin
- CyberPanel'de veritabanının oluşturulduğundan emin olun
- Kullanıcının veritabanına erişim yetkisi olduğunu kontrol edin

### "config dizinine yazılamıyor"
```bash
chmod -R 755 config
chown -R cyberpanel:cyberpanel config
```

### "Sayfa bulunamadı (404)"
- `.htaccess` dosyasının olduğundan emin olun
- Apache mod_rewrite modülünün aktif olduğunu kontrol edin

### "Giriş yapılamıyor"
- Email ve şifrenizi kontrol edin
- Büyük/küçük harf duyarlıdır
- Tarayıcı çerezlerinin açık olduğundan emin olun

## Destek

Sorun yaşarsanız:
- README.md dosyasını okuyun
- GitHub Issues'da arama yapın
- Yeni bir issue açın: https://github.com/alimustpdr/riba-survey-system/issues

## Tebrikler! 🎉

RİBA Anket Sistemi başarıyla kuruldu. Artık anketlerinizi oluşturabilir ve paylaşabilirsiniz!
