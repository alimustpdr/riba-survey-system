<?php
declare(strict_types=1);

namespace App\Models;

final class ScoreCalculator
{
    /**
     * Placeholder for future scoring logic (e.g., RIBA maturity scoring).
     * Keep intentionally minimal for initial scaffold.
     */
    public static function calculate(array $responses): float
    {
        // Example: numeric average of numeric answers
        $sum = 0.0;
        $count = 0;
        foreach ($responses as $r) {
            if (is_numeric($r)) {
                $sum += (float)$r;
                $count++;
            }
        }
        return $count > 0 ? $sum / $count : 0.0;
    }
}
