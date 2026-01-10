<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class SurveyResponse
{
    public static function upsert(int $surveyId, int $questionId, int $userId, string $answerText): void
    {
        $pdo = Database::pdo();

        // MySQL/MariaDB: use ON DUPLICATE KEY UPDATE.
        $stmt = $pdo->prepare(
            'INSERT INTO survey_responses (survey_id, question_id, user_id, answer_text, created_at, updated_at)
             VALUES (:survey_id, :question_id, :user_id, :answer_text, NOW(), NOW())
             ON DUPLICATE KEY UPDATE answer_text = VALUES(answer_text), updated_at = NOW()'
        );

        $stmt->execute([
            'survey_id' => $surveyId,
            'question_id' => $questionId,
            'user_id' => $userId,
            'answer_text' => $answerText,
        ]);
    }
}
