<?php
require_once __DIR__ . '/../includes/admin_bootstrap.php';
csrf_check();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }

$id     = (int)($_POST['id'] ?? 0);
$status = (string)($_POST['status'] ?? '');
$allowed = ['pending_payment','paid','shipped','completed','cancelled'];
if ($id <= 0 || !in_array($status, $allowed, true)) {
    header('Location: /admin/orders.php'); exit;
}

$conn->begin_transaction();
try {
    $st = $conn->prepare("SELECT status FROM shop_orders WHERE id=? FOR UPDATE");
    $st->bind_param('i', $id);
    $st->execute();
    $row = $st->get_result()->fetch_assoc();
    $st->close();
    if (!$row) throw new RuntimeException('订单不存在');
    $old = $row['status'];
    if ($old === $status) { $conn->commit(); header('Location: /admin/orders.php?id='.$id); exit; }

    // 取消 → 恢复库存（仅当之前已扣减：待付款/已付款/已发货都已扣过）
    if ($status === 'cancelled' && $old !== 'cancelled') {
        $items = $conn->query("SELECT product_id, quantity FROM shop_order_items WHERE order_id=$id");
        if ($items) {
            $stUp = $conn->prepare("UPDATE shop_products SET stock = stock + ?, sales_count = GREATEST(0, sales_count - ?) WHERE id=?");
            while ($it = $items->fetch_assoc()) {
                $q = (int)$it['quantity']; $pid = (int)$it['product_id'];
                $stUp->bind_param('iii', $q, $q, $pid);
                $stUp->execute();
            }
            $stUp->close();
        }
    }
    // 从取消复活 → 重新扣减库存
    if ($old === 'cancelled' && $status !== 'cancelled') {
        $items = $conn->query("SELECT product_id, quantity FROM shop_order_items WHERE order_id=$id");
        if ($items) {
            $stUp = $conn->prepare("UPDATE shop_products SET stock = stock - ?, sales_count = sales_count + ? WHERE id=? AND stock >= ?");
            while ($it = $items->fetch_assoc()) {
                $q = (int)$it['quantity']; $pid = (int)$it['product_id'];
                $stUp->bind_param('iiii', $q, $q, $pid, $q);
                $stUp->execute();
                if ($stUp->affected_rows < 1) throw new RuntimeException("库存不足，无法复活订单");
            }
            $stUp->close();
        }
    }

    $set = "status=?";
    $bind = [$status];
    $types = 's';
    if ($status === 'paid')      { $set .= ", paid_at=NOW()"; }
    if ($status === 'shipped')   { $set .= ", shipped_at=NOW()"; }
    if ($status === 'completed') { $set .= ", completed_at=NOW()"; }
    if ($status === 'cancelled') { $set .= ", cancelled_at=NOW()"; }
    $st = $conn->prepare("UPDATE shop_orders SET $set WHERE id=?");
    $bind[] = $id; $types .= 'i';
    $st->bind_param($types, ...$bind);
    $st->execute();
    $st->close();

    $conn->commit();
    $_SESSION['shop_admin_flash'] = '订单状态已更新为：' . $status;
} catch (Throwable $e) {
    $conn->rollback();
    $_SESSION['shop_admin_flash_err'] = '更新失败：' . $e->getMessage();
}

header('Location: /admin/orders.php?id=' . $id);
