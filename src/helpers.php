<?php
// src/helpers.php - Sesiones seguras, protección XSS y tokens CSRF

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// Escapar texto para evitar ataques XSS
function e(string $text): string {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

// Generar token secreto CSRF para proteger formularios
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verificar el token CSRF cuando se envía un formulario
function verify_csrf(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals(csrf_token(), $token)) {
        http_response_code(403);
        exit('Invalid CSRF token. Please refresh the page and try again.');
    }
}

// Comprobar si hay un usuario conectado
function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}

// Bloquear páginas privadas si no has iniciado sesión
function require_login(): void {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

// Bloquear páginas de administrador si no eres admin
function require_admin(): void {
    require_login();
    if (($_SESSION['role'] ?? '') !== 'admin') {
        http_response_code(403);
        exit('Access denied: Administrators only.');
    }
}