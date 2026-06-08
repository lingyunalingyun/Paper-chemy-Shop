<?php
require_once __DIR__ . '/../includes/admin_bootstrap.php';
csrf_check();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }

$id      = (int)($_POST['id'] ?? 0);
$action  = (string)($_POST['action'] ?? '');

if ($action === 'delete' && $id > 0) {
    $conn->query("UPDATE shop_products SET category_id=NULL WHERE category_id=$id");
    $conn->query("DELETE FROM shop_categories WHERE id=$id");
    $_SESSION['shop_admin_flash'] = '分类已删除';
    header('Location: /admin/categories.php'); exit;
}

$name = trim((string)($_POST['name'] ?? ''));
$slug = trim((string)($_POST['slug'] ?? ''));
$sort = (int)($_POST['sort_order'] ?? 0);
$is_active = !empty($_POST['is_active']) ? 1 : ($id > 0 ? 0 : 1);

if ($name === '' || $slug === '' || !preg_match('/^[a-z0-9_\-]+$/i', $slug)) {
    $_SESSION['shop_admin_flash_err'] = '请填写有效的名称与英文 slug';
    header('Location: /admin/categories.php'); exit;
}

if ($id > 0) {
    $st = $conn->prepare("UPDATE shop_categories SET name=?, slug=?, sort_order=?, is_active=? WHERE id=?");
    $st->bind_param('ssiii', $name, $slug, $sort, $is_active, $id);
    $st->execute();
    if ($st->errno) { $_SESSION['shop_admin_flash_err'] = '保存失败：' . $st->error; }
    else { $_SESSION['shop_admin_flash'] = '已保存'; }
    $st->close();
} else {
    $st = $conn->prepare("INSERT INTO shop_categories(name,slug,sort_order,is_active) VALUES(?,?,?,?)");
    $st->bind_param('ssii', $name, $slug, $sort, $is_active);
    $st->execute();
    if ($st->errno) {
        $_SESSION['shop_admin_flash_err'] = $st->errno === 1062 ? 'slug 已存在' : ('新建失败：' . $st->error);
    } else {
        $_SESSION['shop_admin_flash'] = '已新建';
    }
    $st->close();
}

header('Location: /admin/categories.php'); exit;
