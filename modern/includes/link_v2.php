<?php
/**
 * Modern link helpers (v2).
 *
 * This file is additive and does not change existing link behavior.
 * It provides a signed "context blob" that can encode school/class/target-group
 * without modifying survey definitions.
 */

function modern_get_app_secret_bytes(): string {
    if (defined('APP_SECRET') && is_string(APP_SECRET) && APP_SECRET !== '') {
        // APP_SECRET is a hex string in install.php; accept both hex and raw strings safely.
        $s = (string)APP_SECRET;
        if (preg_match('/^[a-f0-9]{32,}$/i', $s)) {
            $raw = @hex2bin($s);
            if (is_string($raw) && $raw !== '') return $raw;
        }
        return $s;
    }
    if (defined('DB_PASS')) {
        return hash('sha256', (string)DB_PASS, true);
    }
    return hash('sha256', 'riba-survey-system-modern', true);
}

function modern_b64url_encode(string $raw): string {
    return rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');
}

function modern_b64url_decode(string $b64url): ?string {
    $b64 = strtr($b64url, '-_', '+/');
    $pad = strlen($b64) % 4;
    if ($pad > 0) $b64 .= str_repeat('=', 4 - $pad);
    $out = base64_decode($b64, true);
    return ($out === false) ? null : $out;
}

/**
 * Build signed context query params for a survey token.
 *
 * Returns array with keys:
 * - ctx (base64url json)
 * - csig (hex hmac)
 */
function modern_sign_context(string $surveyToken, array $ctx): array {
    $json = json_encode($ctx, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (!is_string($json)) $json = '{}';
    $ctx_b64 = modern_b64url_encode($json);
    $data = $surveyToken . '|' . $ctx_b64;
    $csig = hash_hmac('sha256', $data, modern_get_app_secret_bytes());
    return ['ctx' => $ctx_b64, 'csig' => $csig];
}

/**
 * Verify signed context params and return decoded ctx array.
 */
function modern_verify_context(string $surveyToken, string $ctx_b64, string $csig): ?array {
    if ($ctx_b64 === '' || $csig === '') return null;
    $data = $surveyToken . '|' . $ctx_b64;
    $expected = hash_hmac('sha256', $data, modern_get_app_secret_bytes());
    if (!hash_equals($expected, $csig)) return null;
    $json = modern_b64url_decode($ctx_b64);
    if ($json === null) return null;
    $decoded = json_decode($json, true);
    return is_array($decoded) ? $decoded : null;
}

