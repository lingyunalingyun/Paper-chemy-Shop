<?php
/**
 * chemis 端 SSO 起点
 *   GET /sso/start.php?back=/cart.php
 * 把用户跳到论坛 authorize.php，并要求登完跳回 /sso/callback.php?token=...
 * back 仅保留为站内路径，登录成功后由 callback.php 继续跳到该处。
 */
require_once __DIR__ . '/../config.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$back = (string)($_GET['back'] ?? '/');
// 仅允许站内相对路径，防开放重定向
if (!preg_match('#^/[^/]#', $back)) {
    $back = '/';
}
$_SESSION['sso_post_login_back'] = $back;

$return = SSO_CALLBACK_URL;
header('Location: ' . SSO_AUTHORIZE_URL . '?return=' . urlencode($return));
exit;
