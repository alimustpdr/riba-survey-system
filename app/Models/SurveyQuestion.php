<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class SurveyQuestion
{
    /** @return array<int, array<string,mixed>> */
    public static function forSurvey(int $surveyId): array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('SELECT id, survey_id, question_text, sort_order FROM survey_questions WHERE survey_id = :sid ORDER BY sort_order ASC, id ASC');
        $stmt->execute(['sid' => $surveyId]);
        return $stmt->fetchAll() ?: [];
    }

    public static function create(int $surveyId, string $text, int $sortOrder): int
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('INSERT INTO survey_questions (survey_id, question_text, sort_order) VALUES (:sid, :text, :sort_order)');
        $stmt->execute(['sid' => $surveyId, 'text' => $text, 'sort_order' => $sortOrder]);
        return (int)$pdo->lastInsertId();
    }
}
