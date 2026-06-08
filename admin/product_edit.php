<?php
require_once __DIR__ . '/includes/admin_bootstrap.php';

$id = (int)($_GET['id'] ?? 0);
$prod = [
    'id'=>0,'category_id'=>0,'name'=>'','subtitle'=>'','description'=>'',
    'price_cents'=>0,'original_price_cents'=>0,'stock'=>0,'cover'=>'',
    'is_featured'=>0,'is_active'=>1,
];
if ($id > 0) {
    $st = $conn->prepare("SELECT * FROM shop_products WHERE id=?");
    $st->bind_param('i', $id);
    $st->execute();
    $row = $st->get_result()->fetch_assoc();
    $st->close();
    if (!$row) { $_SESSION['shop_admin_flash_err']='商品不存在'; header('Location: /admin/products.php'); exit; }
    $prod = $row;
}

$cats = [];
if ($r = $conn->query("SELECT id,name FROM shop_categories ORDER BY sort_order, id")) {
    while ($row = $r->fetch_assoc()) $cats[] = $row;
}

shop_admin_render_header($id ? '编辑商品' : '新建商品', 'products');
?>
<h1><?= $id ? '编辑商品 #'.$id : '新建商品' ?></h1>

<form action="/admin/actions/product_save.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
    <input type="hidden" name="id" value="<?= (int)$prod['id'] ?>">

    <div class="card">
        <div class="row">
            <div class="col"><label>商品名 *</label>
                <input class="input" name="name" required maxlength="120" value="<?= htmlspecialchars($prod['name']) ?>"></div>
            <div class="col"><label>分类</label>
                <select class="select" name="category_id">
                    <option value="0">— 无分类 —</option>
                    <?php foreach ($cats as $c): ?>
                        <option value="<?= (int)$c['id'] ?>" <?= (int)$prod['category_id']===(int)$c['id']?'selected':'' ?>><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div><label>副标题（一句话说明）</label>
            <input class="input" name="subtitle" maxlength="200" value="<?= htmlspecialchars($prod['subtitle'] ?? '') ?>"></div>
        <div style="margin-top:14px;"><label>详细描述（支持换行）</label>
            <textarea class="textarea" name="description" rows="8"><?= htmlspecialchars($prod['description'] ?? '') ?></textarea></div>
    </div>

    <div class="card">
        <div class="row">
            <div class="col"><label>售价（元，可带小数）*</label>
                <input class="input" name="price_yuan" required type="number" step="0.01" min="0" value="<?= number_format($prod['price_cents']/100, 2, '.', '') ?>"></div>
            <div class="col"><label>原价（元，留空表示无划线价）</label>
                <input class="input" name="original_price_yuan" type="number" step="0.01" min="0" value="<?= $prod['original_price_cents']>0 ? number_format($prod['original_price_cents']/100, 2, '.', '') : '' ?>"></div>
            <div class="col"><label>库存 *</label>
                <input class="input" name="stock" required type="number" min="0" value="<?= (int)$prod['stock'] ?>"></div>
        </div>
    </div>

    <div class="card">
        <label>封面图</label>
        <?php if (!empty($prod['cover'])): ?>
            <div style="margin-bottom:10px;"><img src="<?= htmlspecialchars($prod['cover']) ?>" style="max-width:200px;border-radius:6px;"></div>
            <input class="input" name="cover_url" value="<?= htmlspecialchars($prod['cover']) ?>" placeholder="封面 URL（手填或下面上传）">
        <?php else: ?>
            <input class="input" name="cover_url" placeholder="封面 URL（手填或下面上传）">
        <?php endif; ?>
        <div style="margin-top:10px;">
            <label class="muted">或上传图片（≤ 5MB，jpg/png/webp/gif）</label>
            <input type="file" name="cover_file" accept="image/*">
        </div>
    </div>

    <div class="card">
        <div class="row">
            <div class="col">
                <label style="color:#081828;display:inline-flex;align-items:center;gap:8px;cursor:pointer;">
                    <input type="checkbox" name="is_active" value="1" <?= $prod['is_active']?'checked':'' ?>> 上架销售
                </label>
            </div>
            <div class="col">
                <label style="color:#081828;display:inline-flex;align-items:center;gap:8px;cursor:pointer;">
                    <input type="checkbox" name="is_featured" value="1" <?= $prod['is_featured']?'checked':'' ?>> 设为精选（首页展示）
                </label>
            </div>
        </div>
    </div>

    <div style="display:flex;gap:10px;">
        <button class="btn btn-primary" type="submit">保存</button>
        <a class="btn" href="/admin/products.php">取消</a>
        <?php if ($id): ?>
            <a class="btn" href="/product-details.php?id=<?= $id ?>" target="_blank" style="margin-left:auto;">查看前台 ↗</a>
        <?php endif; ?>
    </div>
</form>

<?php shop_admin_render_footer(); ?>
