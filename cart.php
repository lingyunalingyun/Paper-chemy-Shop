<?php
require_once __DIR__ . '/includes/shop_bootstrap.php';
shop_require_login();

$page_title = '购物车 - 纸片化学';
$active_nav = 'cart';

$uid = (int)$_SESSION['user_id'];
$items = [];
$total_cents = 0;
$total_count = 0;

$res = $conn->query("SELECT c.id AS cart_id, c.quantity, p.id AS pid, p.name, p.cover, p.price_cents, p.original_price_cents, p.stock, p.is_active
                     FROM shop_cart_items c
                     JOIN shop_products p ON p.id = c.product_id
                     WHERE c.user_id = $uid
                     ORDER BY c.id DESC");
if ($res) {
    while ($r = $res->fetch_assoc()) {
        $r['subtotal'] = (int)$r['price_cents'] * (int)$r['quantity'];
        if ($r['is_active'] && $r['stock'] > 0) {
            $total_cents += $r['subtotal'];
            $total_count += (int)$r['quantity'];
        }
        $items[] = $r;
    }
}

$flash = $_SESSION['shop_flash'] ?? '';
unset($_SESSION['shop_flash']);

include __DIR__ . '/includes/header_html.php';
?>

<div class="breadcrumbs">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 col-md-6 col-12">
                <div class="breadcrumbs-content"><h1 class="page-title">购物车</h1></div>
            </div>
            <div class="col-lg-6 col-md-6 col-12">
                <ul class="breadcrumb-nav">
                    <li><a href="/index.php"><i class="lni lni-home"></i> 主页</a></li>
                    <li>购物车</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<section class="shopping-cart section">
    <div class="container">
        <?php if ($flash): ?>
            <div class="alert" style="padding:12px 16px;background:#e8f4ff;color:#0356c7;border-left:4px solid #0167F3;margin-bottom:20px;border-radius:6px;">
                <?= htmlspecialchars($flash) ?>
            </div>
        <?php endif; ?>

        <?php if (!$items): ?>
            <div class="alert" style="padding:60px;background:#fff;color:#666;border:1px dashed #d9dce3;text-align:center;border-radius:6px;">
                购物车空空如也。<a href="/product-grids.php" style="color:#0167F3;">去逛逛 →</a>
            </div>
        <?php else: ?>
        <form action="/actions/cart_update.php" method="post">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
            <div class="cart-list-head">
                <div class="cart-list-title">
                    <div class="row">
                        <div class="col-lg-1 col-md-1 col-12"></div>
                        <div class="col-lg-4 col-md-3 col-12">商品</div>
                        <div class="col-lg-2 col-md-2 col-12">单价</div>
                        <div class="col-lg-2 col-md-2 col-12">数量</div>
                        <div class="col-lg-2 col-md-2 col-12">小计</div>
                        <div class="col-lg-1 col-md-2 col-12">操作</div>
                    </div>
                </div>

                <?php foreach ($items as $it): ?>
                <div class="cart-single-list">
                    <div class="row align-items-center">
                        <div class="col-lg-1 col-md-1 col-12">
                            <a href="/product-details.php?id=<?= (int)$it['pid'] ?>">
                                <img src="<?= htmlspecialchars($it['cover'] ?: '/assets/images/products/product-1.jpg') ?>" alt="" style="max-width:80px;border-radius:4px;">
                            </a>
                        </div>
                        <div class="col-lg-4 col-md-3 col-12">
                            <h5 class="product-name">
                                <a href="/product-details.php?id=<?= (int)$it['pid'] ?>"><?= htmlspecialchars($it['name']) ?></a>
                            </h5>
                            <?php if (!$it['is_active']): ?>
                                <p style="color:#e74c3c;font-size:12px;">已下架，结算时将忽略</p>
                            <?php elseif ($it['stock'] <= 0): ?>
                                <p style="color:#e74c3c;font-size:12px;">已售罄</p>
                            <?php elseif ($it['stock'] < $it['quantity']): ?>
                                <p style="color:#e67e22;font-size:12px;">库存仅剩 <?= (int)$it['stock'] ?> 件</p>
                            <?php endif; ?>
                        </div>
                        <div class="col-lg-2 col-md-2 col-12"><p>￥<?= shop_fmt_price((int)$it['price_cents']) ?></p></div>
                        <div class="col-lg-2 col-md-2 col-12">
                            <input type="number" name="qty[<?= (int)$it['cart_id'] ?>]" value="<?= (int)$it['quantity'] ?>" min="0" max="<?= max(1,(int)$it['stock']) ?>" style="width:80px;padding:6px 10px;background:#fff;border:1px solid #d9dce3;color:#081828;border-radius:4px;">
                        </div>
                        <div class="col-lg-2 col-md-2 col-12"><p style="color:#0167F3;font-weight:bold;">￥<?= shop_fmt_price((int)$it['subtotal']) ?></p></div>
                        <div class="col-lg-1 col-md-2 col-12">
                            <button type="submit" form="remove-<?= (int)$it['cart_id'] ?>" class="remove-item" style="background:none;border:none;color:#e74c3c;cursor:pointer;"><i class="lni lni-close"></i></button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="row" style="margin-top:24px;">
                <div class="col-lg-6 col-12">
                    <button type="submit" class="btn">更新数量</button>
                    <button type="submit" name="action" value="clear" class="btn" style="color:#e74c3c;border:1px solid #e74c3c;background:#fff;" onclick="return confirm('确认清空购物车？')">清空购物车</button>
                    <a href="/product-grids.php" class="btn">继续购物</a>
                </div>
                <div class="col-lg-6 col-12 text-right" style="text-align:right;">
                    <div style="font-size:18px;color:#081828;margin-bottom:12px;">合计（<?= $total_count ?> 件）：<span style="color:#0167F3;font-size:24px;font-weight:bold;">￥<?= shop_fmt_price($total_cents) ?></span></div>
                    <a href="/checkout.php" class="btn" style="background:#0167F3;color:#fff;font-weight:bold;border-color:#0167F3;<?= $total_count<=0?'opacity:0.5;pointer-events:none;':'' ?>">去结算</a>
                </div>
            </div>
        </form>

        <?php foreach ($items as $it): ?>
            <form id="remove-<?= (int)$it['cart_id'] ?>" action="/actions/cart_update.php" method="post" style="display:none;">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                <input type="hidden" name="action" value="remove">
                <input type="hidden" name="cart_id" value="<?= (int)$it['cart_id'] ?>">
            </form>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer_html.php'; ?>
