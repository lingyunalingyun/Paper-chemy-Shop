<?php
require_once __DIR__ . '/../includes/admin_bootstrap.php';
csrf_check();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }

$id          = (int)($_POST['id'] ?? 0);
$name        = trim((string)($_POST['name'] ?? ''));
$subtitle    = trim((string)($_POST['subtitle'] ?? ''));
$description = trim((string)($_POST['description'] ?? ''));
$category_id = (int)($_POST['category_id'] ?? 0) ?: null;
$price_yuan  = (float)($_POST['price_yuan'] ?? 0);
$orig_yuan_raw = trim((string)($_POST['original_price_yuan'] ?? ''));
$stock       = max(0, (int)($_POST['stock'] ?? 0));
$is_active   = !empty($_POST['is_active']) ? 1 : 0;
$is_featured = !empty($_POST['is_featured']) ? 1 : 0;
$cover       = trim((string)($_POST['cover_url'] ?? ''));

if ($name === '') {
    $_SESSION['shop_admin_flash_err'] = '商品名必填';
    header('Location: /admin/product_edit.php' . ($id ? '?id='.$id : '')); exit;
}
if ($price_yuan < 0) $price_yuan = 0;
$price_cents = (int)round($price_yuan * 100);
$orig_cents  = ($orig_yuan_raw === '') ? null : (int)round(((float)$orig_yuan_raw) * 100);

// 处理上传
if (!empty($_FILES['cover_file']['name']) && $_FILES['cover_file']['error'] === UPLOAD_ERR_OK) {
    $tmp = $_FILES['cover_file']['tmp_name'];
    $size = (int)$_FILES['cover_file']['size'];
    if ($size > 5 * 1024 * 1024) {
        $_SESSION['shop_admin_flash_err'] = '图片不能超过 5MB';
        header('Location: /admin/product_edit.php' . ($id ? '?id='.$id : '')); exit;
    }
    $info = @getimagesize($tmp);
    if (!$info) {
        $_SESSION['shop_admin_flash_err'] = '不是有效的图片';
        header('Location: /admin/product_edit.php' . ($id ? '?id='.$id : '')); exit;
    }
    $mime2ext = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];
    $mime = $info['mime'] ?? '';
    if (!isset($mime2ext[$mime])) {
        $_SESSION['shop_admin_flash_err'] = '仅允许 jpg/png/webp/gif';
        header('Location: /admin/product_edit.php' . ($id ? '?id='.$id : '')); exit;
    }
    $ext = $mime2ext[$mime];
    // chemis 根目录下的 uploads/products/ （admin/actions/../../）
    $dir_fs  = dirname(__DIR__, 2) . '/uploads/products';
    $dir_url = '/uploads/products';
    if (!is_dir($dir_fs)) @mkdir($dir_fs, 0775, true);
    $fname = 'p_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    if (!move_uploaded_file($tmp, $dir_fs . '/' . $fname)) {
        $_SESSION['shop_admin_flash_err'] = '上传保存失败';
        header('Location: /admin/product_edit.php' . ($id ? '?id='.$id : '')); exit;
    }
    $cover = $dir_url . '/' . $fname;
}

if ($id > 0) {
    $st = $conn->prepare("UPDATE shop_products
        SET category_id=?, name=?, subtitle=?, description=?, price_cents=?, original_price_cents=?,
            stock=?, cover=?, is_active=?, is_featured=?
        WHERE id=?");
    $cat_param = $category_id; // 允许 null
    $orig_param = $orig_cents;
    $st->bind_param('isssiiisiii',
        $cat_param, $name, $subtitle, $description, $price_cents, $orig_param,
        $stock, $cover, $is_active, $is_featured, $id);
    $st->execute();
    if ($st->errno) {
        $_SESSION['shop_admin_flash_err'] = '保存失败：' . $st->error;
        $st->close();
        header('Location: /admin/product_edit.php?id='.$id); exit;
    }
    $st->close();
    $_SESSION['shop_admin_flash'] = '已保存';
    header('Location: /admin/product_edit.php?id='.$id); exit;
}

$st = $conn->prepare("INSERT INTO shop_products
    (category_id, name, subtitle, description, price_cents, original_price_cents,
     stock, cover, is_active, is_featured)
    VALUES (?,?,?,?,?,?,?,?,?,?)");
$st->bind_param('isssiiisii',
    $category_id, $name, $subtitle, $description, $price_cents, $orig_cents,
    $stock, $cover, $is_active, $is_featured);
$st->execute();
$new_id = (int)$conn->insert_id;
$st->close();
$_SESSION['shop_admin_flash'] = '已新建商品 #' . $new_id;
header('Location: /admin/product_edit.php?id=' . $new_id);
exit;
