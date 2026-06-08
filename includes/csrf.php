<?php
// csrf.php —— CSRF 令牌（与论坛同款实现，独立部署到 chemis 后不再依赖论坛）

function csrf_token(): string {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES) . '">';
}

function csrf_valid(): bool {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $sent = $_POST['csrf'] ?? $_GET['csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    return is_string($sent) && $sent !== '' && !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $sent);
}

function csrf_check(): void {
    if (!csrf_valid()) {
        http_response_code(403);
        echo (stripos($_SERVER['HTTP_ACCEPT'] ?? '', 'json') !== false) ? '{"status":"error","msg":"CSRF 校验失败"}' : 'csrf_error';
        exit;
    }
}
