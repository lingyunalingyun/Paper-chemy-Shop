<?php
require_once __DIR__ . '/../includes/shop_bootstrap.php';
csrf_check();
shop_require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('method not allowed'); }

$uid = (int)$_SESSION['user_id'];

$receiver = trim((string)($_POST['receiver'] ?? ''));
$phone    = trim((string)($_POST['phone'] ?? ''));
$province = trim((string)($_POST['province'] ?? ''));
$city     = trim((string)($_POST['city'] ?? ''));
$district = trim((string)($_POST['district'] ?? ''));
$detail   = trim((string)($_POST['detail'] ?? ''));
$remark   = trim((string)($_POST['remark'] ?? ''));
$save_addr = !empty($_POST['save_address']);

if ($receiver === '' || $phone === '' || $province === '' || $city === '' || $detail === '') {
    $_SESSION['shop_flash'] = '请完整填写收货信息';
    header('Location: /checkout.php'); exit;
}
if (!preg_match('/^1[3-9]\d{9}$/', $phone)) {
    $_SESSION['shop_flash'] = '手机号格式不正确';
    header('Location: /checkout.php'); exit;
}

// 取购物车有效项目（加锁式：在事务中重新核对库存）
$conn->begin_transaction();
try {
    $items = [];
    $total_cents = 0;
    $total_count = 0;

    $res = $conn->query("SELECT c.id AS cart_id, c.quantity, p.id AS pid, p.name, p.cover, p.price_cents, p.stock
                         FROM shop_cart_items c
                         JOIN shop_products p ON p.id = c.product_id
                         WHERE c.user_id = $uid AND p.is_active=1 AND p.stock > 0
                         FOR UPDATE");
    if (!$res || $res->num_rows === 0) throw new RuntimeException('购物车为空');

    while ($r = $res->fetch_assoc()) {
        $qty = min((int)$r['quantity'], (int)$r['stock']);
        if ($qty < 1) continue;
        $r['final_qty'] = $qty;
        $r['subtotal']  = (int)$r['price_cents'] * $qty;
        $items[] = $r;
        $total_cents += $r['subtotal'];
        $total_count += $qty;
    }
    if (!$items) throw new RuntimeException('购物车无有效商品');

    $order_no = shop_gen_order_no($uid);
    $address_snap = $province . ' ' . $city . ' ' . ($district !== '' ? $district . ' ' : '') . $detail;

    $st = $conn->prepare("INSERT INTO shop_orders(order_no,user_id,total_cents,item_count,status,receiver,phone,address_snapshot,remark)
                          VALUES(?,?,?,?, 'pending_payment',?,?,?,?)");
    $st->bind_param('siiissss', $order_no, $uid, $total_cents, $total_count, $receiver, $phone, $address_snap, $remark);
    $st->execute();
    $order_id = (int)$conn->insert_id;
    $st->close();

    $st_item = $conn->prepare("INSERT INTO shop_order_items(order_id,product_id,product_name,product_cover,unit_price_cents,quantity,subtotal_cents) VALUES(?,?,?,?,?,?,?)");
    $st_stock = $conn->prepare("UPDATE shop_products SET stock = stock - ?, sales_count = sales_count + ? WHERE id=? AND stock >= ?");
    foreach ($items as $it) {
        $st_item->bind_param('iissiii', $order_id, $it['pid'], $it['name'], $it['cover'], $it['price_cents'], $it['final_qty'], $it['subtotal']);
        $st_item->execute();

        $st_stock->bind_param('iiii', $it['final_qty'], $it['final_qty'], $it['pid'], $it['final_qty']);
        $st_stock->execute();
        if ($st_stock->affected_rows < 1) throw new RuntimeException('库存不足：' . $it['name']);
    }
    $st_item->close();
    $st_stock->close();

    // 清空已下单的购物车项
    $cart_ids = array_map(fn($i)=>(int)$i['cart_id'], $items);
    $in = implode(',', $cart_ids);
    $conn->query("DELETE FROM shop_cart_items WHERE user_id=$uid AND id IN ($in)");

    // 保存地址（如勾选）
    if ($save_addr) {
        $st = $conn->prepare("INSERT INTO shop_addresses(user_id,receiver,phone,province,city,district,detail,is_default) VALUES(?,?,?,?,?,?,?,0)");
        $st->bind_param('issssss', $uid, $receiver, $phone, $province, $city, $district, $detail);
        $st->execute();
        $st->close();
    }

    $conn->commit();

    $_SESSION['shop_flash'] = '下单成功！订单号 ' . $order_no;
    header('Location: /orders.php?highlight=' . urlencode($order_no));
    exit;

} catch (Throwable $e) {
    $conn->rollback();
    $_SESSION['shop_flash'] = '下单失败：' . $e->getMessage();
    header('Location: /checkout.php');
    exit;
}
