<?php
require_once __DIR__ . '/includes/admin_bootstrap.php';

$page = max(1, (int)($_GET['page'] ?? 1));
$per = 20;
$offset = ($page - 1) * $per;
$q = trim((string)($_GET['q'] ?? ''));

$where = '1=1'; $bind=[]; $types='';
if ($q !== '') { $where .= " AND (p.name LIKE ? OR p.subtitle LIKE ?)"; $kw='%'.$q.'%'; $bind[]=$kw;$bind[]=$kw;$types='ss'; }

$total = 0;
$st = $conn->prepare("SELECT COUNT(*) AS c FROM shop_products p WHERE $where");
if ($bind) $st->bind_param($types, ...$bind);
$st->execute();
$total = (int)$st->get_result()->fetch_assoc()['c'];
$st->close();

$products = [];
$sql = "SELECT p.*, c.name AS cat_name FROM shop_products p LEFT JOIN shop_categories c ON c.id=p.category_id
        WHERE $where ORDER BY p.id DESC LIMIT ? OFFSET ?";
$st = $conn->prepare($sql);
$bind2 = $bind; $bind2[]=$per; $bind2[]=$offset;
$st->bind_param($types.'ii', ...$bind2);
$st->execute();
$res = $st->get_result();
while ($r = $res->fetch_assoc()) $products[] = $r;
$st->close();

$total_pages = max(1, (int)ceil($total / $per));

shop_admin_render_header('商品列表', 'products');
?>
<div style="display:flex;justify-content:space-between;align-items:center;">
    <h1 style="margin:0;">商品（<?= $total ?>）</h1>
    <div>
        <form style="display:inline-flex;gap:8px;" method="get">
            <input class="input" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="搜索商品名" style="width:240px;">
            <button class="btn" type="submit">搜索</button>
        </form>
        <a class="btn btn-primary" href="/admin/product_edit.php" style="margin-left:8px;">+ 新建商品</a>
    </div>
</div>

<table style="margin-top:20px;">
    <thead><tr><th>ID</th><th>封面</th><th>名称</th><th>分类</th><th>价格</th><th>库存</th><th>销量</th><th>状态</th><th>操作</th></tr></thead>
    <tbody>
    <?php if (!$products): ?>
        <tr><td colspan="9" class="muted" style="text-align:center;padding:30px;">还没有商品。<a href="/admin/product_edit.php">立即新建</a></td></tr>
    <?php else: foreach ($products as $p): ?>
        <tr>
            <td><?= (int)$p['id'] ?></td>
            <td><?php if ($p['cover']): ?><img src="<?= htmlspecialchars($p['cover']) ?>" style="width:50px;height:50px;object-fit:cover;border-radius:4px;"><?php else: ?><span class="muted">—</span><?php endif; ?></td>
            <td>
                <div><?= htmlspecialchars($p['name']) ?></div>
                <?php if ($p['is_featured']): ?><span class="badge" style="background:#0167F315;color:#0167F3;">精选</span><?php endif; ?>
            </td>
            <td class="muted"><?= htmlspecialchars($p['cat_name'] ?? '未分类') ?></td>
            <td>￥<?= shop_fmt_price((int)$p['price_cents']) ?></td>
            <td><?= (int)$p['stock'] ?></td>
            <td><?= (int)$p['sales_count'] ?></td>
            <td>
                <?php if ($p['is_active']): ?>
                    <span class="badge" style="background:#0167F315;color:#0167F3;">在售</span>
                <?php else: ?>
                    <span class="badge" style="background:#f0f2f5;color:#888;">下架</span>
                <?php endif; ?>
            </td>
            <td class="actions" style="white-space:nowrap;">
                <a class="btn" href="/admin/product_edit.php?id=<?= (int)$p['id'] ?>">编辑</a>
                <form action="/admin/actions/product_toggle.php" method="post" style="display:inline;">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                    <input type="hidden" name="field" value="is_active">
                    <button class="btn" type="submit"><?= $p['is_active']?'下架':'上架' ?></button>
                </form>
                <form action="/admin/actions/product_toggle.php" method="post" style="display:inline;">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                    <input type="hidden" name="field" value="is_featured">
                    <button class="btn" type="submit"><?= $p['is_featured']?'取消精选':'设为精选' ?></button>
                </form>
                <form action="/admin/actions/product_delete.php" method="post" style="display:inline;" onsubmit="return confirm('确认删除？');">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                    <button class="btn btn-danger" type="submit">删除</button>
                </form>
            </td>
        </tr>
    <?php endforeach; endif; ?>
    </tbody>
</table>

<?php if ($total_pages > 1): ?>
<div style="margin-top:20px;">
    <?php for ($i=1; $i<=$total_pages; $i++):
        $u = '/admin/products.php?page='.$i . ($q!==''?'&q='.urlencode($q):''); ?>
        <a class="btn <?= $i===$page?'btn-primary':'' ?>" href="<?= htmlspecialchars($u) ?>"><?= $i ?></a>
    <?php endfor; ?>
</div>
<?php endif; ?>

<?php shop_admin_render_footer(); ?>
