<?php
/**
 * Best-effort schema ensure for Modern UI.
 *
 * This is additive: only uses CREATE TABLE IF NOT EXISTS.
 * It does NOT modify existing survey definitions or install logic.
 */

function modern_ensure_response_context_table(PDO $pdo): void {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS response_context (
            id INT AUTO_INCREMENT PRIMARY KEY,
            response_id INT NOT NULL,
            survey_id INT NOT NULL,
            school_id INT NULL,
            class_id INT NULL,
            class_name VARCHAR(50) NULL,
            kademe VARCHAR(20) NULL,
            role VARCHAR(20) NULL,
            target_group VARCHAR(20) NULL,
            scope VARCHAR(20) NULL,
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
}

function modern_parse_grade_branch_from_class_name(?string $className): array {
    $className = trim((string)$className);
    if ($className === '') return ['grade' => null, 'branch' => null];

    // Common patterns:
    // - "5/A" or "5-A"
    // - "10 B" or "10B"
    if (preg_match('/^\\s*(\\d{1,2})\\s*[\\/-\\s]?\\s*([A-Za-zÇĞİÖŞÜçğıöşü])\\s*$/u', $className, $m)) {
        return ['grade' => $m[1], 'branch' => mb_strtoupper($m[2], 'UTF-8')];
    }
    if (preg_match('/\\b(\\d{1,2})\\s*[\\/-\\s]?\\s*([A-Za-zÇĞİÖŞÜçğıöşü])\\b/u', $className, $m)) {
        return ['grade' => $m[1], 'branch' => mb_strtoupper($m[2], 'UTF-8')];
    }
    return ['grade' => null, 'branch' => null];
}

