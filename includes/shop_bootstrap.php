<?php
// 每个 chemis-shop 入口都 require 它：
//   require_once __DIR__ . '/includes/shop_bootstrap.php';
// 提供：$conn、session、建表、当前登录信息、CSRF、SSO 跳转工具

require_once __DIR__ . '/../config.php';       // chemis 自己的 DB + SSO 配置
require_once __DIR__ . '/csrf.php';            // chemis 自己的 csrf
require_once __DIR__ . '/shop_db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

shop_ensure_tables($conn);

$shop_user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
$shop_is_logged_in = $shop_user_id > 0;

/** 当前请求未登录则发起 SSO 跳转（去论坛 authorize.php） */
if (!function_exists('shop_require_login')) {
function shop_require_login(): void {
    if (empty($_SESSION['user_id'])) {
        $back = urlencode($_SERVER['REQUEST_URI'] ?? '/');
        header('Location: /sso/start.php?back=' . $back);
        exit;
    }
}}

/** 取本地镜像 users 表中的当前登录用户 */
if (!function_exists('shop_current_user')) {
function shop_current_user(mysqli $conn): ?array {
    if (empty($_SESSION['user_id'])) return null;
    $uid = (int)$_SESSION['user_id'];
    $res = $conn->query("SELECT id, username, avatar, avatar_url, points, role, mid FROM users WHERE id=$uid LIMIT 1");
    return $res ? ($res->fetch_assoc() ?: null) : null;
}}

/** 取当前用户购物车合计件数 */
if (!function_exists('shop_cart_count')) {
function shop_cart_count(mysqli $conn): int {
    if (empty($_SESSION['user_id'])) return 0;
    $uid = (int)$_SESSION['user_id'];
    $res = $conn->query("SELECT COALESCE(SUM(quantity),0) AS n FROM shop_cart_items WHERE user_id=$uid");
    return $res ? (int)$res->fetch_assoc()['n'] : 0;
}}
