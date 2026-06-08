<?php
require_once __DIR__ . '/../includes/admin_bootstrap.php';
csrf_check();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) { header('Location: /admin/products.php'); exit; }

// 若存在订单项引用该商品，仅软下架；否则真删
$ref_res = $conn->query("SELECT COUNT(*) AS c FROM shop_order_items WHERE product_id=$id");
$referenced = $ref_res ? (int)$ref_res->fetch_assoc()['c'] > 0 : false;

if ($referenced) {
    $conn->query("UPDATE shop_products SET is_active=0 WHERE id=$id");
    $_SESSION['shop_admin_flash'] = '该商品有历史订单引用，已改为下架（未删除）';
} else {
    $conn->query("DELETE FROM shop_cart_items WHERE product_id=$id");
    $conn->query("DELETE FROM shop_product_images WHERE product_id=$id");
    $conn->query("DELETE FROM shop_products WHERE id=$id");
    $_SESSION['shop_admin_flash'] = '已删除';
}
header('Location: /admin/products.php');
