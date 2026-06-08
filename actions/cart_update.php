<?php
require_once __DIR__ . '/../includes/shop_bootstrap.php';
csrf_check();
shop_require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('method not allowed'); }

$uid = (int)$_SESSION['user_id'];
$action = (string)($_POST['action'] ?? 'update');

if ($action === 'remove') {
    $cid = (int)($_POST['cart_id'] ?? 0);
    if ($cid > 0) {
        $st = $conn->prepare("DELETE FROM shop_cart_items WHERE id=? AND user_id=?");
        $st->bind_param('ii', $cid, $uid);
        $st->execute(); $st->close();
    }
    $_SESSION['shop_flash'] = '已移除';
    header('Location: /cart.php'); exit;
}

if ($action === 'clear') {
    $conn->query("DELETE FROM shop_cart_items WHERE user_id=$uid");
    $_SESSION['shop_flash'] = '购物车已清空';
    header('Location: /cart.php'); exit;
}

// 默认：批量更新数量
$quantities = $_POST['qty'] ?? [];
if (is_array($quantities)) {
    $st_get = $conn->prepare("SELECT p.stock FROM shop_cart_items c JOIN shop_products p ON p.id=c.product_id WHERE c.id=? AND c.user_id=?");
    $st_upd = $conn->prepare("UPDATE shop_cart_items SET quantity=? WHERE id=? AND user_id=?");
    $st_del = $conn->prepare("DELETE FROM shop_cart_items WHERE id=? AND user_id=?");
    foreach ($quantities as $cid => $q) {
        $cid = (int)$cid; $q = (int)$q;
        if ($cid <= 0) continue;
        if ($q <= 0) {
            $st_del->bind_param('ii', $cid, $uid);
            $st_del->execute();
            continue;
        }
        $st_get->bind_param('ii', $cid, $uid);
        $st_get->execute();
        $row = $st_get->get_result()->fetch_assoc();
        if (!$row) continue;
        $q = min($q, (int)$row['stock']);
        if ($q < 1) $q = 1;
        $st_upd->bind_param('iii', $q, $cid, $uid);
        $st_upd->execute();
    }
    $st_get->close(); $st_upd->close(); $st_del->close();
}

$_SESSION['shop_flash'] = '购物车已更新';
header('Location: /cart.php'); exit;
