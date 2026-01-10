<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;

final class SurveyController extends Controller
{
    /** @param array<string,string> $params */
    public function index(array $params = []): void
    {
        $this->requireAuth();
        $surveys = Survey::all();
        $this->view('surveys/list', [
            'title' => 'Surveys',
            'surveys' => $surveys,
        ]);
    }

    /** @param array<string,string> $params */
    public function create(array $params = []): void
    {
        $this->requireRole('admin');
        $this->view('surveys/create', [
            'title' => 'Create Survey',
            'error' => $_SESSION['flash_error'] ?? null,
        ]);
        unset($_SESSION['flash_error']);
    }

    /** @param array<string,string> $params */
    public function store(array $params = []): void
    {
        $this->requireRole('admin');
        verify_csrf();

        $title = trim((string)($_POST['title'] ?? ''));
        $questionsRaw = trim((string)($_POST['questions'] ?? ''));

        if ($title === '' || $questionsRaw === '') {
            $_SESSION['flash_error'] = 'Title and questions are required.';
            $this->redirect('/surveys/create');
        }

        $surveyId = Survey::create($title, (int)current_user()['id']);

        $lines = preg_split('/\R/', $questionsRaw) ?: [];
        $order = 1;
        foreach ($lines as $line) {
            $q = trim($line);
            if ($q === '') continue;
            SurveyQuestion::create($surveyId, $q, $order++);
        }

        $this->redirect('/surveys');
    }

    /** @param array<string,string> $params */
    public function answer(array $params = []): void
    {
        $this->requireAuth();
        $id = (int)($params['id'] ?? 0);
        $survey = Survey::find($id);
        if (!$survey) {
            http_response_code(404);
            echo 'Survey not found.';
            return;
        }
        $questions = SurveyQuestion::forSurvey($id);
        $this->view('surveys/answer', [
            'title' => 'Answer Survey',
            'survey' => $survey,
            'questions' => $questions,
            'success' => $_SESSION['flash_success'] ?? null,
            'error' => $_SESSION['flash_error'] ?? null,
        ]);
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);
    }

    /** @param array<string,string> $params */
    public function submitAnswer(array $params = []): void
    {
        $this->requireAuth();
        verify_csrf();

        $surveyId = (int)($params['id'] ?? 0);
        $survey = Survey::find($surveyId);
        if (!$survey) {
            http_response_code(404);
            echo 'Survey not found.';
            return;
        }

        $answers = $_POST['answers'] ?? [];
        if (!is_array($answers) || $answers === []) {
            $_SESSION['flash_error'] = 'Please answer at least one question.';
            $this->redirect('/surveys/' . $surveyId . '/answer');
        }

        $userId = (int)current_user()['id'];
        foreach ($answers as $questionId => $value) {
            $qid = (int)$questionId;
            $val = trim((string)$value);
            if ($qid <= 0 || $val === '') continue;
            SurveyResponse::upsert($surveyId, $qid, $userId, $val);
        }

        $_SESSION['flash_success'] = 'Responses saved.';
        $this->redirect('/surveys/' . $surveyId . '/answer');
    }
}
