<?php
// 兼容旧链接：访问 /login.php 时直接走 SSO 起点
$back = $_GET['back'] ?? '/index.php';
header('Location: /sso/start.php?back=' . urlencode($back), true, 302);
exit;
