<?php
require_once __DIR__ . '/../includes/admin_bootstrap.php';
csrf_check();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }

$id    = (int)($_POST['id'] ?? 0);
$field = (string)($_POST['field'] ?? '');
if ($id <= 0 || !in_array($field, ['is_active','is_featured'], true)) {
    header('Location: /admin/products.php'); exit;
}
$conn->query("UPDATE shop_products SET `$field` = 1 - `$field` WHERE id=$id");
$_SESSION['shop_admin_flash'] = '已切换';
header('Location: /admin/products.php');
