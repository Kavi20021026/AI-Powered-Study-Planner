<?php
/**
 * config.php
 *
 * This file loads on every request.
 * It starts the session, defines application constants,
 * and boots the shared helper functions.
 *
 * This version of the project uses:
 * - PHP sessions for login state and flash messages
 * - MySQL for users, subjects, settings, and study plans
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => 1,
        'cookie_secure' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 1 : 0,
        'cookie_samesite' => 'Lax'
    ]);
}

date_default_timezone_set('Asia/Colombo');

define('APP_NAME', 'AI-Powered Study Planner');
define('APP_WEIGHT', 10);
define('DEFAULT_DAILY_HOURS', 4.0);
define('DEFAULT_REMINDER_TIME', '18:00:00');

define('DB_HOST', getenv('STUDY_PLANNER_DB_HOST') ?: 'localhost');
define('DB_USER', getenv('STUDY_PLANNER_DB_USER') ?: 'root');
define('DB_PASS', getenv('STUDY_PLANNER_DB_PASS') ?: '');
define('DB_NAME', getenv('STUDY_PLANNER_DB_NAME') ?: 'study_planner_db');

/**
 * Detect the base URL so links continue to work in localhost.
 */
function detectBaseUrl(string $projectPath): string
{
    $documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
    $realProjectPath = realpath($projectPath) ?: $projectPath;
    $realDocumentRoot = realpath($documentRoot) ?: $documentRoot;

    $normalizedProjectPath = str_replace('\\', '/', $realProjectPath);
    $normalizedDocumentRoot = rtrim(str_replace('\\', '/', $realDocumentRoot), '/');

    if ($normalizedDocumentRoot !== '' && strpos($normalizedProjectPath, $normalizedDocumentRoot) === 0) {
        $relativePath = substr($normalizedProjectPath, strlen($normalizedDocumentRoot));
        return $relativePath === '' ? '' : $relativePath;
    }

    return '/study-planner';
}

define('APP_BASE_URL', rtrim(detectBaseUrl(__DIR__), '/'));

require_once __DIR__ . '/includes/functions.php';

initializeAppState();
