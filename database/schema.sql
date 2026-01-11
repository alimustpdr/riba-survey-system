-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'school_admin') NOT NULL,
    school_id INT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Schools Table
CREATE TABLE IF NOT EXISTS schools (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    user_id INT NOT NULL,
    package_id INT NULL,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    expire_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Packages Table
CREATE TABLE IF NOT EXISTS packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    duration_months INT NOT NULL,
    max_surveys INT DEFAULT 0,
    features TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Form Templates (11 RİBA Forms)
CREATE TABLE IF NOT EXISTS form_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kademe ENUM('okuloncesi', 'ilkokul', 'ortaokul', 'lise') NOT NULL,
    role ENUM('ogrenci', 'veli', 'ogretmen') NOT NULL,
    questions JSON NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Surveys Table
CREATE TABLE IF NOT EXISTS surveys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT NOT NULL,
    form_template_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    token VARCHAR(64) UNIQUE NOT NULL,
    status ENUM('active', 'closed') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (form_template_id) REFERENCES form_templates(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Responses Table
CREATE TABLE IF NOT EXISTS responses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    survey_id INT NOT NULL,
    class_name VARCHAR(100) NULL,
    response_data JSON NOT NULL,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (survey_id) REFERENCES surveys(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payments Table
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT NOT NULL,
    package_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    payment_method VARCHAR(50),
    transaction_id VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (package_id) REFERENCES packages(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert 11 RİBA Form Templates with all questions
-- 1. Okul Öncesi - Veli
INSERT INTO form_templates (kademe, role, questions) VALUES 
('okuloncesi', 'veli', JSON_ARRAY(
    JSON_OBJECT('id', 1, 'A', 'Arkadaşlarıyla iş birliği yaparak ve paylaşarak oynamayı öğrenme', 'B', 'Sorunlarını nasıl çözebileceğini öğrenme (ör., çözüm yolları üretme, yardım isteme)'),
    JSON_OBJECT('id', 2, 'A', 'Tehlikeli olabilecek durumlarda (ör., bahçede koşma, yüksekten atlama, sivri cisimler kullanma) dikkatli davranmayı öğrenme', 'B', 'İrade geliştirmeyle ilgili temel düzeyde beceriler kazanma (ör., bir oyuncak satın almak için para biriktirmek)'),
    JSON_OBJECT('id', 3, 'A', 'Öfkesini kontrol etmeyi öğrenme', 'B', 'Sınıf kuralları hakkında bilgilenme'),
    JSON_OBJECT('id', 4, 'A', 'İncitici bir davranışla (kötü söz söyleme, alay etme, vurma vb.) karşılaştığında ne yapması gerektiğini öğrenme', 'B', 'Kişisel özellikleriyle değerli bir birey olduğunu hissetme'),
    JSON_OBJECT('id', 5, 'A', 'Özgüven kazanma konusunda desteklenme (ör., kararlarını bağımsız verme, yalnızken bile kendini güvende hissetme)', 'B', 'Başkaları hatırlatmadan sorumluluklarını yerine getirebilme becerisi kazanma (ör., oyuncaklarını toplama, tabağını masadan kaldırma)'),
    JSON_OBJECT('id', 6, 'A', 'Duygularını (ör., mutluluk, üzüntü, korku ve şaşkınlık) tanıma', 'B', 'Blok, oyun hamuru gibi materyaller ile yaratıcılıklarını kullanarak kendini ifade etme'),
    JSON_OBJECT('id', 7, 'A', 'Rehber öğretmeni/psikolojik danışmanı tanıma ve ondan hangi konularda yardım alacaklarını öğrenme', 'B', 'İletişim becerileri kazanma (ör., söz kesmeden dinlemek, soru sorma ve uyarıları dikkate alma)'),
    JSON_OBJECT('id', 8, 'A', 'Arkadaş edinme ve arkadaşlarıyla iyi geçinme (ör., kavga etmeden oyun oynama, oyuncaklarını paylaşma)', 'B', 'Karar vermeyle ilgili temel düzeyde beceriler kazanma (ör., verilen seçenekler arasından uygun olanı seçme)'),
    JSON_OBJECT('id', 9, 'A', 'Zamanı planlamayı öğrenme (ör., uyuma, dinlenme, oyun oynama süresi)', 'B', 'Mesleklerin toplumdaki önemini fark etme ve olumlu tutum geliştirme (ör., itfaiyeci hayat kurtarmaktadır)'),
    JSON_OBJECT('id', 10, 'A', 'İhtiyacı olduğunda doğru kişilerden yardım isteme (ör., incitici bir davranışla karşılaştığında öğretmeninden yardım isteme)', 'B', 'Duygu ve düşüncelerini ifade etme'),
    JSON_OBJECT('id', 11, 'A', '"HAYIR!" diyebilmeyi öğrenme (ör., bir şey yapmak istemediğinde ya da tehlikeli durumlardan kaçınması gerektiğinde)', 'B', 'Bireysel farklılıklara (karşı cins, özel gereksinimli birey ve göçmenler) saygılı davranmayı öğrenme'),
    JSON_OBJECT('id', 12, 'A', 'Sağlıklı yaşam, kişisel bakım ve hijyen konusunda bilgilenme (ör., sağlıklı beslenme, ellerini yıkama)', 'B', 'İstismardan korunmayı öğrenme'),
    JSON_OBJECT('id', 13, 'A', 'Dikkatini odaklama ve sürdürme becerileri kazanma', 'B', 'Tablet, televizyon ve telefonu kullanırken ailenin belirlediği içeriklere ve kullanım süresine uyma')
));

-- 2. Okul Öncesi - Öğretmen
INSERT INTO form_templates (kademe, role, questions) VALUES 
('okuloncesi', 'ogretmen', JSON_ARRAY(
    JSON_OBJECT('id', 1, 'A', 'Arkadaşlarıyla iş birliği yaparak ve paylaşarak oynamayı öğrenme', 'B', 'Problem çözme becerilerini öğrenme'),
    JSON_OBJECT('id', 2, 'A', 'Okulda fiziksel güvenliklerini sağlayacak davranışlar kazanma', 'B', 'İrade geliştirmeyle ilgili temel düzeyde beceriler kazanma (ör., bir oyuncak satın almak için para biriktirmek)'),
    JSON_OBJECT('id', 3, 'A', 'Öfke kontrolüyle ilgili temel düzeyde beceriler kazanma', 'B', 'Okul ve sınıf kuralları hakkında bilgilenme'),
    JSON_OBJECT('id', 4, 'A', 'İncitici bir davranışla (kötü söz söyleme, alay etme, vurma vb.) karşılaştığında ne yapması gerektiğini öğrenme', 'B', 'Kendilerine özgü özellikleriyle değerli bireyler olduklarını hissetme'),
    JSON_OBJECT('id', 5, 'A', 'Özgüven kazanma konusunda desteklenme', 'B', 'Başkaları hatırlatmadan sorumluluklarını yerine getirebilme becerisi kazanma (ör., oyuncaklarını toplama, tabağını masadan kaldırma)'),
    JSON_OBJECT('id', 6, 'A', 'Duygularını (ör., mutluluk, üzüntü, korku ve şaşkınlık) tanıma', 'B', 'Blok, oyun hamuru gibi materyaller ile yaratıcılıklarını kullanarak kendini ifade etme'),
    JSON_OBJECT('id', 7, 'A', 'Rehber öğretmeni/psikolojik danışmanı tanıma ve ondan hangi konularda yardım alacaklarını öğrenme', 'B', 'İletişim becerileri kazanma (ör., söz kesmeden dinlemek, soru sorma ve yönergeleri takip etme)'),
    JSON_OBJECT('id', 8, 'A', 'Arkadaş edinme ve arkadaşlarıyla iyi geçinme', 'B', 'Karar vermeyle ilgili temel düzeyde beceriler kazanma'),
    JSON_OBJECT('id', 9, 'A', 'Zamanı planlamayı öğrenme', 'B', 'Mesleklerin toplumdaki önemini fark etme ve olumlu tutum geliştirme'),
    JSON_OBJECT('id', 10, 'A', 'Yardım arama becerilerini geliştirme (ör., nereden ve kimden yardım isteyeceğini bilme)', 'B', 'Duygu ve düşüncelerini ifade etme'),
    JSON_OBJECT('id', 11, 'A', 'İlişkilerinde kişisel sınırlarını koruma (ör., "HAYIR!" deme becerisi)', 'B', 'Bireysel farklılıklara (ör., karşı cinsiyet, engelli öğrenci ve göçmenler) saygılı davranmayı öğrenme'),
    JSON_OBJECT('id', 12, 'A', 'Sağlıklı yaşam, kişisel bakım ve hijyen konusunda bilgilenme', 'B', 'İstismardan korunmayı öğrenme'),
    JSON_OBJECT('id', 13, 'A', 'Dikkatini odaklama ve sürdürme becerileri kazanma', 'B', 'Okuryazarlığa hazırlık çalışmalarında hazırbulunuşluklarını destekleme')
));