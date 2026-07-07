<?php
/**
 * Session bootstrap + auth helpers.
 * Included at the top of every protected page and API endpoint.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * BASE_URL — auto-detected so the app works whether it's placed at the
 * web server root or inside a subfolder (e.g. XAMPP's htdocs/signal).
 * Computed once here since every page includes this file first.
 */
if (!defined('BASE_URL')) {
    $docRoot = str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/'));
    $appRoot = str_replace('\\', '/', dirname(__DIR__));
    $baseUrl = '';
    if ($docRoot !== '' && str_starts_with($appRoot, $docRoot)) {
        $baseUrl = substr($appRoot, strlen($docRoot));
    }
    define('BASE_URL', rtrim($baseUrl, '/'));
}

function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/auth/login.php');
        exit;
    }
}

function requireLoginJson(): void
{
    if (!isLoggedIn()) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Not authenticated.']);
        exit;
    }
}

function currentUserId(): int
{
    return (int) $_SESSION['user_id'];
}
