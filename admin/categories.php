<?php
require_once __DIR__ . '/includes/admin_bootstrap.php';

$cats = [];
if ($r = $conn->query("SELECT c.*, (SELECT COUNT(*) FROM shop_products p WHERE p.category_id=c.id) AS pn FROM shop_categories c ORDER BY c.sort_order, c.id")) {
    while ($row = $r->fetch_assoc()) $cats[] = $row;
}

shop_admin_render_header('分类管理', 'categories');
?>
<h1>商品分类</h1>

<div class="card">
    <h3 style="margin-top:0;">新建分类</h3>
    <form action="/admin/actions/category_save.php" method="post">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
        <div class="row">
            <div class="col"><label>名称</label><input class="input" name="name" required maxlength="60"></div>
            <div class="col"><label>slug（英文短代号）</label><input class="input" name="slug" required maxlength="60" pattern="[a-z0-9\-_]+"></div>
            <div class="col"><label>排序值（小在前）</label><input class="input" name="sort_order" type="number" value="0"></div>
            <div class="col" style="flex:0 0 100px;align-self:flex-end;"><button class="btn btn-primary" type="submit">新建</button></div>
        </div>
    </form>
</div>

<table>
    <thead><tr><th>ID</th><th>名称</th><th>slug</th><th>商品数</th><th>排序</th><th>状态</th><th>操作</th></tr></thead>
    <tbody>
        <?php foreach ($cats as $c): ?>
            <tr>
                <td><?= (int)$c['id'] ?></td>
                <td>
                    <form action="/admin/actions/category_save.php" method="post" style="display:flex;gap:6px;align-items:center;">
                        <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                        <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                        <input class="input" name="name" value="<?= htmlspecialchars($c['name']) ?>" style="width:auto;">
                </td>
                <td><input class="input" name="slug" value="<?= htmlspecialchars($c['slug']) ?>" style="width:auto;"></td>
                <td><?= (int)$c['pn'] ?></td>
                <td><input class="input" name="sort_order" value="<?= (int)$c['sort_order'] ?>" type="number" style="width:80px;"></td>
                <td>
                    <label style="color:#081828;display:inline;">
                        <input type="checkbox" name="is_active" value="1" <?= $c['is_active']?'checked':'' ?>> 启用
                    </label>
                </td>
                <td class="actions">
                    <button class="btn btn-primary" type="submit">保存</button>
                    </form>
                    <form action="/admin/actions/category_save.php" method="post" style="display:inline;" onsubmit="return confirm('确认删除？该分类下商品将变为未分类。');">
                        <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                        <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                        <input type="hidden" name="action" value="delete">
                        <button class="btn btn-danger" type="submit">删除</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php shop_admin_render_footer(); ?>
