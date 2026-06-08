<?php
require_once __DIR__ . '/includes/shop_bootstrap.php';
shop_require_login();

$page_title = '我的订单 - 纸片化学';
$active_nav = '';
$uid = (int)$_SESSION['user_id'];
$highlight = (string)($_GET['highlight'] ?? '');

$orders = [];
$res = $conn->query("SELECT * FROM shop_orders WHERE user_id=$uid ORDER BY id DESC LIMIT 50");
if ($res) { while ($r = $res->fetch_assoc()) $orders[] = $r; }

// 一次性把所有订单的 items 拉出来分组
$items_by_order = [];
if ($orders) {
    $ids = implode(',', array_map(fn($o)=>(int)$o['id'], $orders));
    $res2 = $conn->query("SELECT * FROM shop_order_items WHERE order_id IN ($ids) ORDER BY id");
    if ($res2) {
        while ($r = $res2->fetch_assoc()) {
            $items_by_order[(int)$r['order_id']][] = $r;
        }
    }
}

$flash = $_SESSION['shop_flash'] ?? '';
unset($_SESSION['shop_flash']);

$status_label = [
    'pending_payment' => ['待付款', '#e67e22'],
    'paid'            => ['已付款', '#0167F3'],
    'shipped'         => ['已发货', '#3498db'],
    'completed'       => ['已完成', '#27ae60'],
    'cancelled'       => ['已取消', '#e74c3c'],
];

include __DIR__ . '/includes/header_html.php';
?>

<div class="breadcrumbs">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 col-md-6 col-12">
                <div class="breadcrumbs-content"><h1 class="page-title">我的订单</h1></div>
            </div>
            <div class="col-lg-6 col-md-6 col-12">
                <ul class="breadcrumb-nav">
                    <li><a href="/index.php"><i class="lni lni-home"></i> 主页</a></li>
                    <li>我的订单</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<section class="my-orders section">
    <div class="container">
        <?php if ($flash): ?>
            <div class="alert" style="padding:12px 16px;background:#e8f4ff;color:#0356c7;border-left:4px solid #0167F3;margin-bottom:20px;border-radius:6px;">
                <?= htmlspecialchars($flash) ?>
            </div>
        <?php endif; ?>

        <?php if (!$orders): ?>
            <div class="alert" style="padding:60px;background:#fff;color:#666;border:1px dashed #d9dce3;text-align:center;border-radius:6px;">
                还没有订单。<a href="/product-grids.php" style="color:#0167F3;">去逛逛 →</a>
            </div>
        <?php else: foreach ($orders as $o):
            $lbl = $status_label[$o['status']] ?? [$o['status'], '#888'];
            $is_hl = $highlight !== '' && $o['order_no'] === $highlight;
        ?>
        <div style="background:#fff;border:1px solid <?= $is_hl?'#0167F3':'#e6e8ed' ?>;border-radius:8px;margin-bottom:20px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,.04);">
            <div style="display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #f0f2f5;padding-bottom:12px;margin-bottom:12px;">
                <div>
                    <span style="color:#888;font-size:12px;">订单号</span>
                    <span style="color:#081828;font-family:monospace;margin-left:8px;"><?= htmlspecialchars($o['order_no']) ?></span>
                    <span style="color:#aaa;font-size:12px;margin-left:16px;"><?= htmlspecialchars($o['created_at']) ?></span>
                </div>
                <div>
                    <span style="display:inline-block;padding:4px 12px;border-radius:12px;background:<?= $lbl[1] ?>15;color:<?= $lbl[1] ?>;font-size:12px;font-weight:500;"><?= htmlspecialchars($lbl[0]) ?></span>
                </div>
            </div>
            <div>
                <?php foreach ($items_by_order[(int)$o['id']] ?? [] as $it): ?>
                <div style="display:flex;align-items:center;padding:8px 0;color:#081828;">
                    <img src="<?= htmlspecialchars($it['product_cover'] ?: '/assets/images/products/product-1.jpg') ?>" style="width:50px;height:50px;border-radius:4px;margin-right:12px;object-fit:cover;">
                    <div style="flex:1;"><?= htmlspecialchars($it['product_name']) ?> × <?= (int)$it['quantity'] ?></div>
                    <div style="color:#0167F3;">￥<?= shop_fmt_price((int)$it['subtotal_cents']) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <div style="border-top:1px solid #f0f2f5;padding-top:12px;margin-top:12px;display:flex;justify-content:space-between;align-items:center;">
                <div style="color:#888;font-size:13px;">
                    收货：<?= htmlspecialchars($o['receiver']) ?> <?= htmlspecialchars($o['phone']) ?><br>
                    <?= htmlspecialchars($o['address_snapshot']) ?>
                </div>
                <div style="font-size:18px;">
                    合计 <span style="color:#0167F3;font-weight:bold;font-size:22px;">￥<?= shop_fmt_price((int)$o['total_cents']) ?></span>
                </div>
            </div>
            <?php if (!empty($o['remark'])): ?>
                <div style="color:#aaa;font-size:12px;margin-top:8px;">备注：<?= htmlspecialchars($o['remark']) ?></div>
            <?php endif; ?>
        </div>
        <?php endforeach; endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer_html.php'; ?>
