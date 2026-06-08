<?php
require_once __DIR__ . '/../includes/shop_bootstrap.php';
csrf_check();
shop_require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('method not allowed'); }

$uid = (int)$_SESSION['user_id'];
$pid = (int)($_POST['product_id'] ?? 0);
$qty = max(1, (int)($_POST['quantity'] ?? 1));

if ($pid <= 0) { header('Location: /product-grids.php'); exit; }

// 校验商品存在 + 库存
$st = $conn->prepare("SELECT id, stock FROM shop_products WHERE id=? AND is_active=1 LIMIT 1");
$st->bind_param('i', $pid);
$st->execute();
$prod = $st->get_result()->fetch_assoc();
$st->close();
if (!$prod) { $_SESSION['shop_flash'] = '商品不存在或已下架'; header('Location: /product-grids.php'); exit; }
if ((int)$prod['stock'] <= 0) { $_SESSION['shop_flash'] = '商品已售罄'; header('Location: /product-details.php?id=' . $pid); exit; }

// 已有则累加，否则插入
$st = $conn->prepare("SELECT id, quantity FROM shop_cart_items WHERE user_id=? AND product_id=? LIMIT 1");
$st->bind_param('ii', $uid, $pid);
$st->execute();
$row = $st->get_result()->fetch_assoc();
$st->close();

$max_qty = (int)$prod['stock'];
if ($row) {
    $new_qty = min($max_qty, (int)$row['quantity'] + $qty);
    $st = $conn->prepare("UPDATE shop_cart_items SET quantity=? WHERE id=?");
    $st->bind_param('ii', $new_qty, $row['id']);
    $st->execute();
    $st->close();
} else {
    $new_qty = min($max_qty, $qty);
    $st = $conn->prepare("INSERT INTO shop_cart_items(user_id, product_id, quantity) VALUES(?,?,?)");
    $st->bind_param('iii', $uid, $pid, $new_qty);
    $st->execute();
    $st->close();
}

$_SESSION['shop_flash'] = '已加入购物车';
header('Location: /cart.php');
exit;
