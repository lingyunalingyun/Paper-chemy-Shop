<?php
require_once __DIR__ . '/includes/admin_bootstrap.php';

$stats = [
    'products'    => 0,
    'active'      => 0,
    'orders'      => 0,
    'pending_pay' => 0,
    'today_sales' => 0,
];

if ($r = $conn->query("SELECT COUNT(*) AS c FROM shop_products")) $stats['products'] = (int)$r->fetch_assoc()['c'];
if ($r = $conn->query("SELECT COUNT(*) AS c FROM shop_products WHERE is_active=1")) $stats['active'] = (int)$r->fetch_assoc()['c'];
if ($r = $conn->query("SELECT COUNT(*) AS c FROM shop_orders")) $stats['orders'] = (int)$r->fetch_assoc()['c'];
if ($r = $conn->query("SELECT COUNT(*) AS c FROM shop_orders WHERE status='pending_payment'")) $stats['pending_pay'] = (int)$r->fetch_assoc()['c'];
if ($r = $conn->query("SELECT COALESCE(SUM(total_cents),0) AS s FROM shop_orders WHERE status IN ('paid','shipped','completed') AND DATE(created_at)=CURDATE()")) $stats['today_sales'] = (int)$r->fetch_assoc()['s'];

$recent_orders = [];
if ($r = $conn->query("SELECT o.id, o.order_no, o.total_cents, o.status, o.created_at, u.username
                       FROM shop_orders o JOIN users u ON u.id=o.user_id ORDER BY o.id DESC LIMIT 10")) {
    while ($row = $r->fetch_assoc()) $recent_orders[] = $row;
}

shop_admin_render_header('仪表盘', 'dashboard');
?>
<h1>仪表盘</h1>
<div class="row">
    <div class="col card"><div class="muted">商品总数</div><h2 style="margin:8px 0;color:#0167F3;font-size:28px;"><?= $stats['products'] ?></h2>在售 <?= $stats['active'] ?> 件</div>
    <div class="col card"><div class="muted">订单总数</div><h2 style="margin:8px 0;color:#0167F3;font-size:28px;"><?= $stats['orders'] ?></h2>待付款 <span style="color:#e67e22;"><?= $stats['pending_pay'] ?></span> 单</div>
    <div class="col card"><div class="muted">今日成交额</div><h2 style="margin:8px 0;color:#0167F3;font-size:28px;">￥<?= shop_fmt_price($stats['today_sales']) ?></h2>付款/发货/完成订单</div>
</div>

<div class="card">
    <h3 style="margin-top:0;">最近订单</h3>
    <?php if (!$recent_orders): ?>
        <p class="muted">还没有订单。</p>
    <?php else: ?>
    <table>
        <thead><tr><th>订单号</th><th>用户</th><th>金额</th><th>状态</th><th>时间</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($recent_orders as $o): ?>
            <tr>
                <td style="font-family:monospace;"><?= htmlspecialchars($o['order_no']) ?></td>
                <td><?= htmlspecialchars($o['username']) ?></td>
                <td style="color:#0167F3;">￥<?= shop_fmt_price((int)$o['total_cents']) ?></td>
                <td><?= htmlspecialchars($o['status']) ?></td>
                <td class="muted"><?= htmlspecialchars($o['created_at']) ?></td>
                <td><a class="btn" href="/admin/orders.php?id=<?= (int)$o['id'] ?>">详情</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
<?php shop_admin_render_footer(); ?>
