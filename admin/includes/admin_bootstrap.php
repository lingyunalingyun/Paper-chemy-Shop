<?php
// chemis 后台公共引导：未登录走 SSO，仅 admin/owner 可进
require_once __DIR__ . '/../../includes/shop_bootstrap.php';

if (empty($_SESSION['user_id'])) {
    header('Location: /sso/start.php?back=' . urlencode($_SERVER['REQUEST_URI'] ?? '/admin/'));
    exit;
}

$role = (string)($_SESSION['role'] ?? 'user');
if (!in_array($role, ['owner', 'admin'], true)) {
    http_response_code(403);
    echo '<!doctype html><meta charset="utf-8"><title>无权访问</title><body style="background:#f6f7fb;color:#081828;font-family:\'Roboto\',\'Microsoft YaHei\',sans-serif;padding:80px;text-align:center;">';
    echo '<h2 style="color:#e74c3c;">无权访问 shop 后台</h2><p style="color:#666;">需要 admin / owner 角色。</p>';
    echo '<p style="margin-top:20px;"><a href="/index.php" style="color:#0167F3;">← 返回商城首页</a></p>';
    echo '</body>';
    exit;
}

function shop_admin_render_header(string $title, string $active_tab = ''): void {
    $tabs = [
        'dashboard'  => ['仪表盘',   '/admin/index.php'],
        'products'   => ['商品',     '/admin/products.php'],
        'categories' => ['分类',     '/admin/categories.php'],
        'orders'     => ['订单',     '/admin/orders.php'],
    ];
    ?>
<!doctype html>
<html lang="zh-CN"><head>
<meta charset="utf-8"><title><?= htmlspecialchars($title) ?> · Shop 后台</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="/assets/css/LineIcons.3.0.css">
<style>
*{box-sizing:border-box}
body{margin:0;background:#f6f7fb;color:#081828;font-family:'Roboto','Microsoft YaHei','PingFang SC',sans-serif;font-size:14px;line-height:1.6}
a{color:#0167F3;text-decoration:none}a:hover{color:#0356c7;text-decoration:underline}
.top{background:#fff;border-bottom:1px solid #e6e8ed;padding:14px 28px;display:flex;align-items:center;gap:20px;box-shadow:0 1px 3px rgba(0,0,0,0.04)}
.top .brand{color:#0167F3;font-weight:bold;font-size:16px}
.top nav a{color:#081828;margin-right:6px;padding:8px 14px;border-radius:6px;font-weight:500}
.top nav a:hover{background:#f0f4ff;color:#0167F3;text-decoration:none}
.top nav a.active{background:#0167F3;color:#fff}
.top .right{margin-left:auto;color:#888;font-size:13px}
.top .right a{color:#0167F3}
.wrap{max-width:1200px;margin:0 auto;padding:28px}
h1,h2,h3{color:#081828;font-weight:600;margin-top:0}
h1{font-size:24px;margin-bottom:20px}
table{width:100%;border-collapse:collapse;background:#fff;border:1px solid #e6e8ed;border-radius:8px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.04)}
th,td{padding:12px 16px;text-align:left;border-bottom:1px solid #f0f2f5;vertical-align:middle}
th{background:#f6f7fb;color:#666;font-weight:600;font-size:12px;text-transform:uppercase;letter-spacing:0.5px}
tr:last-child td{border-bottom:none}
tr:hover td{background:#fafbfd}
.btn{display:inline-block;padding:7px 16px;background:#fff;color:#081828;border:1px solid #d9dce3;border-radius:6px;cursor:pointer;text-decoration:none;font-family:inherit;font-size:13px;font-weight:500;transition:all .15s;line-height:1.4}
.btn:hover{background:#f0f4ff;border-color:#0167F3;color:#0167F3;text-decoration:none}
.btn-primary{background:#0167F3;color:#fff;border-color:#0167F3}
.btn-primary:hover{background:#0356c7;border-color:#0356c7;color:#fff}
.btn-danger{color:#e74c3c;border-color:#e74c3c;background:#fff}
.btn-danger:hover{background:#e74c3c;color:#fff;border-color:#e74c3c}
.input,.select,.textarea{width:100%;padding:9px 12px;background:#fff;border:1px solid #d9dce3;color:#081828;border-radius:6px;font-family:inherit;font-size:14px;transition:border-color .15s}
.input:focus,.select:focus,.textarea:focus{outline:none;border-color:#0167F3;box-shadow:0 0 0 3px rgba(1,103,243,.1)}
.card{background:#fff;border:1px solid #e6e8ed;border-radius:8px;padding:24px;margin-bottom:20px;box-shadow:0 1px 3px rgba(0,0,0,0.04)}
.flash{padding:12px 18px;background:#e8f4ff;border-left:4px solid #0167F3;color:#0356c7;border-radius:6px;margin-bottom:18px}
.flash.err{background:#fdecea;border-color:#e74c3c;color:#c0392b}
.muted{color:#888;font-size:12px}
.badge{display:inline-block;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:500}
.row{display:flex;gap:18px;flex-wrap:wrap}
.col{flex:1;min-width:200px}
label{display:block;margin-bottom:6px;color:#081828;font-size:13px;font-weight:500}
.actions a,.actions button{margin-right:6px}
</style></head><body>
<div class="top">
    <div class="brand"><i class="lni lni-cart-full"></i> 纸片化学后台</div>
    <nav>
        <?php foreach ($tabs as $k => $t):
            $cls = $k === $active_tab ? 'active' : ''; ?>
            <a class="<?= $cls ?>" href="<?= $t[1] ?>"><?= $t[0] ?></a>
        <?php endforeach; ?>
    </nav>
    <div class="right">
        <?= htmlspecialchars($_SESSION['username'] ?? '') ?>
        · <a href="/index.php">商城前台</a>
        · <a href="https://musetreehouse.com/admin.php" target="_blank">论坛后台</a>
        · <a href="/sso/logout.php">退出</a>
    </div>
</div>
<div class="wrap">
    <?php
    $flash = $_SESSION['shop_admin_flash'] ?? '';
    $flash_err = $_SESSION['shop_admin_flash_err'] ?? '';
    unset($_SESSION['shop_admin_flash'], $_SESSION['shop_admin_flash_err']);
    if ($flash): ?><div class="flash"><?= htmlspecialchars($flash) ?></div><?php endif;
    if ($flash_err): ?><div class="flash err"><?= htmlspecialchars($flash_err) ?></div><?php endif;
}

function shop_admin_render_footer(): void {
    echo '</div></body></html>';
}
