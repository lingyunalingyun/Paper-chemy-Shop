<?php
// ===================================================================
// chemis-shop 独立项目配置
// 部署：paperchemis.top（独立 Linux + LAMP）
// 账号系统：通过 SSO 与 musetreehouse.com 论坛打通
// ===================================================================

// ─── 数据库连接 ────────────────────────────────
// 在新主机上申请的数据库账号密码，部署前填好
$servername  = "localhost";
$username_db = "TODO_DB_USER";
$password_db = "TODO_DB_PASS";
$dbname      = "TODO_DB_USER";

$conn = new mysqli($servername, $username_db, $password_db);
if ($conn->connect_error) {
    die('数据库连接失败：' . $conn->connect_error);
}
$conn->query("CREATE DATABASE IF NOT EXISTS `$dbname` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$conn->select_db($dbname);
$conn->set_charset("utf8mb4");

// ─── 站点基础 ──────────────────────────────────
define('SITE_URL',  'https://paperchemis.top');
define('SITE_NAME', '纸片化学官方周边');

// ─── SSO 配置 ─────────────────────────────────
// 论坛侧的 SSO 端点
define('SSO_AUTHORIZE_URL', 'https://musetreehouse.com/sso/authorize.php');
define('SSO_VERIFY_API',    'https://musetreehouse.com/sso/verify_api.php');
define('SSO_LOGOUT_URL',    'https://musetreehouse.com/pages/logout.php');

// 与论坛共享的密钥（必须和论坛 config.php 中的 SSO_SHARED_SECRET 完全一致）
define('SSO_SHARED_SECRET', '01ceb2cb414939787daa8cd2e7b2793fdb97d6b05ca94daa5bf337453dbc4ce9');

// chemis 端 callback URL（论坛会把用户跳回这里 + ?token=xxx）
define('SSO_CALLBACK_URL', SITE_URL . '/sso/callback.php');

// session cookie 设置（chemis 自己的域）
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure',   '1');   // 上线时必须 HTTPS
ini_set('session.cookie_samesite', 'Lax');
