<?php
/**
 * chemis 端 SSO 回调
 *   GET /sso/callback.php?token={64-hex}
 *
 * 流程：
 *   1. 拿 token，cURL POST 到论坛 verify_api.php（Header: X-SSO-Secret）
 *   2. 论坛验 token + 返回用户信息 JSON
 *   3. chemis 端 upsert 本地 users 镜像表
 *   4. 写 session（user_id / username / role / mid）
 *   5. 302 跳到之前存的 sso_post_login_back
 */
require_once __DIR__ . '/../includes/shop_bootstrap.php';

$token = trim((string)($_GET['token'] ?? ''));
if (strlen($token) !== 64 || !ctype_xdigit($token)) {
    http_response_code(400);
    exit('Invalid token');
}

// ─── 调论坛 verify API ──────────────────────────
$ch = curl_init(SSO_VERIFY_API);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => http_build_query(['token' => $token]),
    CURLOPT_HTTPHEADER     => [
        'X-SSO-Secret: ' . SSO_SHARED_SECRET,
        'Accept: application/json',
    ],
    CURLOPT_CONNECTTIMEOUT => 5,
    CURLOPT_TIMEOUT        => 10,
    CURLOPT_SSL_VERIFYPEER => true,
]);
$resp_body = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_err  = curl_error($ch);
curl_close($ch);

if ($resp_body === false) {
    http_response_code(502);
    exit('SSO verify failed: ' . htmlspecialchars($curl_err));
}

$data = json_decode($resp_body, true);
if (!is_array($data) || empty($data['ok']) || empty($data['user']['id'])) {
    http_response_code(403);
    $err = $data['error'] ?? 'unknown';
    exit('SSO 验证失败（' . htmlspecialchars($err) . '）');
}

$u = $data['user'];

// ─── upsert 本地 users 镜像 ────────────────────
$id         = (int)$u['id'];
$mid        = (string)($u['mid'] ?? '');
$username   = (string)$u['username'];
$email      = (string)($u['email'] ?? '');
$role       = (string)$u['role'];
$avatar     = (string)($u['avatar'] ?? '');
$avatar_url = (string)($u['avatar_url'] ?? '');
$points     = (int)($u['points'] ?? 0);
$exp        = (int)($u['exp'] ?? 0);
$level      = (int)($u['level'] ?? 1);

$st = $conn->prepare("INSERT INTO users
    (id, mid, username, email, role, avatar, avatar_url, points, exp, level)
    VALUES (?,?,?,?,?,?,?,?,?,?)
    ON DUPLICATE KEY UPDATE
        mid=VALUES(mid), username=VALUES(username), email=VALUES(email),
        role=VALUES(role), avatar=VALUES(avatar), avatar_url=VALUES(avatar_url),
        points=VALUES(points), exp=VALUES(exp), level=VALUES(level),
        last_sync_at=CURRENT_TIMESTAMP");
// 类型说明：id(int) mid(s) username(s) email(s) role(s) avatar(s) avatar_url(s) points(i) exp(i) level(i)
$st->bind_param('issssssiii',
    $id, $mid, $username, $email, $role, $avatar, $avatar_url,
    $points, $exp, $level);
$st->execute();
if ($st->errno) {
    error_log('SSO mirror upsert failed: ' . $st->error);
    http_response_code(500);
    exit('本地用户写入失败');
}
$st->close();

// ─── 建立本地 session ──────────────────────────
session_regenerate_id(true);
$_SESSION['user_id']  = $id;
$_SESSION['username'] = $username;
$_SESSION['role']     = $role;
$_SESSION['mid']      = $mid;

// ─── 跳回业务页 ────────────────────────────────
$back = $_SESSION['sso_post_login_back'] ?? '/';
unset($_SESSION['sso_post_login_back']);
if (!preg_match('#^/[^/]#', $back)) $back = '/';
header('Location: ' . $back);
exit;
