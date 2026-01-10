<?php
declare(strict_types=1);

require dirname(__DIR__) . '/config/bootstrap.php';

use App\Core\Router;

$router = new Router();

// Auth
$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login');
$router->post('/logout', 'AuthController@logout');

// Dashboard
$router->get('/', 'DashboardController@index');
$router->get('/dashboard', 'DashboardController@index');

// Surveys
$router->get('/surveys', 'SurveyController@index');
$router->get('/surveys/create', 'SurveyController@create');
$router->post('/surveys/create', 'SurveyController@store');
$router->get('/surveys/{id}/answer', 'SurveyController@answer');
$router->post('/surveys/{id}/answer', 'SurveyController@submitAnswer');

$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
