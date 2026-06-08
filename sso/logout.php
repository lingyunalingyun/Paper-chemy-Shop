<?php
/**
 * chemis 端 SSO 退出
 *   GET /sso/logout.php
 * 仅清 chemis 本地 session；论坛侧 session 由用户自己在论坛退出
 * （如要级联登出，可改为跳 SSO_LOGOUT_URL）
 */
require_once __DIR__ . '/../config.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();

header('Location: /index.php');
exit;
