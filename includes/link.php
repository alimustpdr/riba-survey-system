<?php
/**
 * Link signing helpers for class-specific survey links.
 *
 * We sign links so users cannot tamper with class_id.
 */
function get_app_secret(): string {
    // Prefer explicit APP_SECRET, fallback to DB_PASS-derived secret for backward compatibility.
    if (defined('APP_SECRET') && is_string(APP_SECRET) && APP_SECRET !== '') {
        return APP_SECRET;
    }
    if (defined('DB_PASS')) {
        return hash('sha256', (string)DB_PASS, true);
    }
    // Last resort (should never happen in real installs)
    return hash('sha256', 'riba-survey-system', true);
}

function sign_survey_class_token(string $survey_token, int $class_id): string {
    $data = $survey_token . '|' . (string)$class_id;
    return hash_hmac('sha256', $data, get_app_secret());
}

function verify_survey_class_token(string $survey_token, int $class_id, string $sig): bool {
    if ($sig === '') return false;
    $expected = sign_survey_class_token($survey_token, $class_id);
    return hash_equals($expected, $sig);
}

