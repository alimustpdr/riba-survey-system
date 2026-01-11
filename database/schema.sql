-- KULLANICILAR TABLOSU
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'school_admin') NOT NULL,
    school_id INT NULL,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- OKULLAR TABLOSU
CREATE TABLE IF NOT EXISTS schools (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    user_id INT NOT NULL,
    package_id INT NULL,
    status ENUM('active', 'expired', 'suspended') DEFAULT 'active',
    expire_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- PAKETLER TABLOSU (Süper Admin Ayarlar)
CREATE TABLE IF NOT EXISTS packages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    max_students INT NULL COMMENT 'NULL = sınırsız',
    max_surveys INT NULL COMMENT 'NULL = sınırsız',
    duration_days INT DEFAULT 365,
    features TEXT NULL COMMENT 'JSON format',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- FORM ŞABLONLARı (11 Adet Standart RİBA Formu)
CREATE TABLE IF NOT EXISTS form_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kademe ENUM('okuloncesi', 'ilkokul', 'ortaokul', 'lise') NOT NULL,
    role ENUM('ogrenci', 'veli', 'ogretmen') NOT NULL,
    title VARCHAR(255) NOT NULL,
    questions JSON NOT NULL COMMENT 'Array of questions with A and B options',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_form (kademe, role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ANKETLER TABLOSU
CREATE TABLE IF NOT EXISTS surveys (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    form_template_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    academic_year VARCHAR(20) NOT NULL COMMENT '2024-2025',
    link_token VARCHAR(64) UNIQUE NOT NULL,
    status ENUM('draft', 'active', 'closed') DEFAULT 'draft',
    target_count INT NOT NULL COMMENT 'Hedef katılımcı sayısı',
    response_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (form_template_id) REFERENCES form_templates(id),
    INDEX idx_token (link_token),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- CEVAPLAR TABLOSU
CREATE TABLE IF NOT EXISTS responses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    survey_id INT NOT NULL,
    class_name VARCHAR(50) NULL COMMENT 'Sınıf bilgisi (ör: 3-A)',
    response_data JSON NOT NULL COMMENT 'Array of answers: [{question_id: 1, answer: A}, ...]',
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (survey_id) REFERENCES surveys(id) ON DELETE CASCADE,
    INDEX idx_survey (survey_id),
    INDEX idx_class (class_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SINIF SONUÇLARI (Otomatik Hesaplanan)
CREATE TABLE IF NOT EXISTS class_results (
    id INT PRIMARY KEY AUTO_INCREMENT,
    survey_id INT NOT NULL,
    class_name VARCHAR(50) NOT NULL,
    results JSON NOT NULL COMMENT 'İstatistiksel sonuçlar',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (survey_id) REFERENCES surveys(id) ON DELETE CASCADE,
    UNIQUE KEY unique_class_result (survey_id, class_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- OKUL SONUÇLARI (Otomatik Hesaplanan)
CREATE TABLE IF NOT EXISTS school_results (
    id INT PRIMARY KEY AUTO_INCREMENT,
    survey_id INT NOT NULL,
    results JSON NOT NULL COMMENT 'Tüm okul geneli istatistikler',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (survey_id) REFERENCES surveys(id) ON DELETE CASCADE,
    UNIQUE KEY unique_school_result (survey_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ÖDEMELER TABLOSU
CREATE TABLE IF NOT EXISTS payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    package_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('iyzico', 'bank_transfer', 'other') DEFAULT 'iyzico',
    payment_token VARCHAR(255) NULL,
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    payment_data JSON NULL COMMENT 'Payment gateway response',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (package_id) REFERENCES packages(id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;