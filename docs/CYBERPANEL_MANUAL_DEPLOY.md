# CyberPanel (Git Manager) ile Manuel Deploy — Adım Adım

Bu proje “auto deploy” olmadan da güvenli şekilde güncellenebilir.

## 1) GitHub tarafı (kodun sunucuya gelmesi için şart)
Sunucu **sadece GitHub’daki branch’i** çeker. Bu yüzden önce:
- Değişiklikleri **commit** et
- GitHub’a **push** et

> PR zorunlu değildir. PR sadece branch’ten `main`’e merge ihtiyacın varsa gerekir.

## 2) CyberPanel tarafı (Pull)
1. CyberPanel’e gir
2. **Git Manager** → ilgili repo/site
3. **Pull** tıkla
4. File Manager’dan kontrol et:
   - Site kökünde `modern/` klasörü var mı?

## 3) Modern arayüzü açma
- Ana giriş: `/` (otomatik `/modern/`’a yönlendirir)
- Klasik ana sayfa: `/index.php?classic=1`
- Modern panel: `/modern/`
- Modern setup: `/modern/setup.php`

## 4) Modern için ek tablo (önerilir)
Modern bağlam raporları için `response_context` tablosu gerekir.

Kolay yol:
- `/modern/setup.php` aç → “Kurulumu Çalıştır”

Manuel yol:
- phpMyAdmin → veritabanını seç → `modern/migrations/001_response_context.sql` içeriğini çalıştır

