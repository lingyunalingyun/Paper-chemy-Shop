<?php
require_once __DIR__ . '/includes/admin_bootstrap.php';

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    // 详情视图
    $st = $conn->prepare("SELECT o.*, u.username, u.mid FROM shop_orders o JOIN users u ON u.id=o.user_id WHERE o.id=?");
    $st->bind_param('i', $id);
    $st->execute();
    $order = $st->get_result()->fetch_assoc();
    $st->close();
    if (!$order) { $_SESSION['shop_admin_flash_err']='订单不存在'; header('Location: /admin/orders.php'); exit; }
    $items = [];
    if ($r = $conn->query("SELECT * FROM shop_order_items WHERE order_id=$id ORDER BY id")) {
        while ($row = $r->fetch_assoc()) $items[] = $row;
    }
    shop_admin_render_header('订单 ' . $order['order_no'], 'orders');
    ?>
    <a href="/admin/orders.php" class="btn">← 返回列表</a>
    <h1 style="margin-top:14px;">订单 <span style="font-family:monospace;color:#0167F3;"><?= htmlspecialchars($order['order_no']) ?></span></h1>

    <div class="card">
        <div class="row">
            <div class="col"><label>用户</label><div><?= htmlspecialchars($order['username']) ?> <span class="muted">(MID: <?= htmlspecialchars($order['mid'] ?? '—') ?>)</span></div></div>
            <div class="col"><label>下单时间</label><div class="muted"><?= htmlspecialchars($order['created_at']) ?></div></div>
            <div class="col"><label>状态</label><div><?= htmlspecialchars($order['status']) ?></div></div>
            <div class="col"><label>合计</label><div style="color:#0167F3;font-size:18px;font-weight:bold;">￥<?= shop_fmt_price((int)$order['total_cents']) ?></div></div>
        </div>
        <hr style="border:none;border-top:1px solid #e6e8ed;margin:14px 0;">
        <div class="row">
            <div class="col"><label>收件人</label><div><?= htmlspecialchars($order['receiver']) ?> <?= htmlspecialchars($order['phone']) ?></div></div>
            <div class="col" style="flex:2;"><label>收货地址</label><div><?= htmlspecialchars($order['address_snapshot']) ?></div></div>
        </div>
        <?php if (!empty($order['remark'])): ?>
            <div style="margin-top:10px;"><label>备注</label><div><?= htmlspecialchars($order['remark']) ?></div></div>
        <?php endif; ?>
    </div>

    <div class="card">
        <h3 style="margin-top:0;">商品清单</h3>
        <table>
            <thead><tr><th></th><th>商品</th><th>单价</th><th>数量</th><th>小计</th></tr></thead>
            <tbody>
            <?php foreach ($items as $it): ?>
                <tr>
                    <td><?php if ($it['product_cover']): ?><img src="<?= htmlspecialchars($it['product_cover']) ?>" style="width:40px;height:40px;object-fit:cover;border-radius:4px;"><?php endif; ?></td>
                    <td><?= htmlspecialchars($it['product_name']) ?></td>
                    <td>￥<?= shop_fmt_price((int)$it['unit_price_cents']) ?></td>
                    <td><?= (int)$it['quantity'] ?></td>
                    <td style="color:#0167F3;">￥<?= shop_fmt_price((int)$it['subtotal_cents']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <h3 style="margin-top:0;">改变状态</h3>
        <form action="/admin/actions/order_status.php" method="post" style="display:flex;gap:10px;align-items:center;">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
            <input type="hidden" name="id" value="<?= (int)$order['id'] ?>">
            <select class="select" name="status" style="width:auto;">
                <?php foreach (['pending_payment'=>'待付款','paid'=>'已付款','shipped'=>'已发货','completed'=>'已完成','cancelled'=>'已取消'] as $k=>$v): ?>
                    <option value="<?= $k ?>" <?= $order['status']===$k?'selected':'' ?>><?= $v ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-primary" type="submit">更新</button>
            <span class="muted">取消订单会自动恢复库存</span>
        </form>
    </div>
    <?php
    shop_admin_render_footer();
    exit;
}

// 列表视图
$status = (string)($_GET['status'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$per = 20;
$offset = ($page - 1) * $per;

$where = '1=1'; $bind=[]; $types='';
if (in_array($status, ['pending_payment','paid','shipped','completed','cancelled'], true)) {
    $where .= " AND o.status=?"; $bind[]=$status; $types.='s';
}

$total = 0;
$st = $conn->prepare("SELECT COUNT(*) AS c FROM shop_orders o WHERE $where");
if ($bind) $st->bind_param($types, ...$bind);
$st->execute();
$total = (int)$st->get_result()->fetch_assoc()['c'];
$st->close();

$orders = [];
$sql = "SELECT o.*, u.username FROM shop_orders o JOIN users u ON u.id=o.user_id
        WHERE $where ORDER BY o.id DESC LIMIT ? OFFSET ?";
$st = $conn->prepare($sql);
$bind2 = $bind; $bind2[]=$per; $bind2[]=$offset;
$st->bind_param($types.'ii', ...$bind2);
$st->execute();
$res = $st->get_result();
while ($r = $res->fetch_assoc()) $orders[] = $r;
$st->close();

$total_pages = max(1, (int)ceil($total / $per));

shop_admin_render_header('订单管理', 'orders');
?>
<h1>订单（<?= $total ?>）</h1>

<div style="margin-bottom:14px;">
    <a class="btn <?= $status===''?'btn-primary':'' ?>" href="/admin/orders.php">全部</a>
    <?php foreach (['pending_payment'=>'待付款','paid'=>'已付款','shipped'=>'已发货','completed'=>'已完成','cancelled'=>'已取消'] as $k=>$v): ?>
        <a class="btn <?= $status===$k?'btn-primary':'' ?>" href="/admin/orders.php?status=<?= $k ?>"><?= $v ?></a>
    <?php endforeach; ?>
</div>

<table>
    <thead><tr><th>订单号</th><th>用户</th><th>金额</th><th>件数</th><th>状态</th><th>时间</th><th></th></tr></thead>
    <tbody>
    <?php if (!$orders): ?>
        <tr><td colspan="7" class="muted" style="text-align:center;padding:30px;">无订单</td></tr>
    <?php else: foreach ($orders as $o): ?>
        <tr>
            <td style="font-family:monospace;"><?= htmlspecialchars($o['order_no']) ?></td>
            <td><?= htmlspecialchars($o['username']) ?></td>
            <td style="color:#0167F3;">￥<?= shop_fmt_price((int)$o['total_cents']) ?></td>
            <td><?= (int)$o['item_count'] ?></td>
            <td><?= htmlspecialchars($o['status']) ?></td>
            <td class="muted"><?= htmlspecialchars($o['created_at']) ?></td>
            <td><a class="btn" href="/admin/orders.php?id=<?= (int)$o['id'] ?>">详情</a></td>
        </tr>
    <?php endforeach; endif; ?>
    </tbody>
</table>

<?php if ($total_pages > 1): ?>
<div style="margin-top:20px;">
    <?php for ($i=1; $i<=$total_pages; $i++):
        $u = '/admin/orders.php?page='.$i . ($status!==''?'&status='.urlencode($status):''); ?>
        <a class="btn <?= $i===$page?'btn-primary':'' ?>" href="<?= htmlspecialchars($u) ?>"><?= $i ?></a>
    <?php endfor; ?>
</div>
<?php endif; ?>

<?php shop_admin_render_footer(); ?>
