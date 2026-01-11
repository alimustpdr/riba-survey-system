-- Additive context storage for modern UI submissions.
-- Safe: does not modify existing tables; no destructive operations.

CREATE TABLE IF NOT EXISTS response_context (
    id INT AUTO_INCREMENT PRIMARY KEY,
    response_id INT NOT NULL,
    survey_id INT NOT NULL,
    school_id INT NULL,
    class_id INT NULL,
    class_name VARCHAR(50) NULL,
    kademe VARCHAR(20) NULL,
    role VARCHAR(20) NULL,
    target_group VARCHAR(20) NULL,  -- student / parent / teacher
    scope VARCHAR(20) NULL,         -- class / school
    grade VARCHAR(20) NULL,
    branch VARCHAR(20) NULL,
    ctx_json JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_response (response_id),
    INDEX idx_survey (survey_id),
    INDEX idx_school (school_id),
    INDEX idx_class (class_id),
    INDEX idx_grade (grade),
    INDEX idx_target (target_group)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

