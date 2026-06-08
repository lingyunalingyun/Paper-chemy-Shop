<?php
require_once __DIR__ . '/includes/shop_bootstrap.php';
shop_require_login();

$page_title = '结算 - 纸片化学';
$active_nav = 'cart';
$uid = (int)$_SESSION['user_id'];

// 取购物车有效项目（is_active=1 且 stock>0）
$items = [];
$total_cents = 0;
$total_count = 0;
$res = $conn->query("SELECT c.id AS cart_id, c.quantity, p.id AS pid, p.name, p.cover, p.price_cents, p.stock
                     FROM shop_cart_items c
                     JOIN shop_products p ON p.id = c.product_id
                     WHERE c.user_id = $uid AND p.is_active=1 AND p.stock > 0
                     ORDER BY c.id DESC");
if ($res) {
    while ($r = $res->fetch_assoc()) {
        $eff_qty = min((int)$r['quantity'], (int)$r['stock']);
        $r['effective_qty'] = $eff_qty;
        $r['subtotal'] = (int)$r['price_cents'] * $eff_qty;
        $total_cents += $r['subtotal'];
        $total_count += $eff_qty;
        $items[] = $r;
    }
}

if (!$items) {
    header('Location: /cart.php'); exit;
}

// 取该用户的地址
$addresses = [];
$res = $conn->query("SELECT * FROM shop_addresses WHERE user_id=$uid ORDER BY is_default DESC, id DESC");
if ($res) { while ($r = $res->fetch_assoc()) $addresses[] = $r; }

$flash = $_SESSION['shop_flash'] ?? '';
unset($_SESSION['shop_flash']);

include __DIR__ . '/includes/header_html.php';
?>

<div class="breadcrumbs">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 col-md-6 col-12">
                <div class="breadcrumbs-content"><h1 class="page-title">结算下单</h1></div>
            </div>
            <div class="col-lg-6 col-md-6 col-12">
                <ul class="breadcrumb-nav">
                    <li><a href="/index.php"><i class="lni lni-home"></i> 主页</a></li>
                    <li><a href="/cart.php">购物车</a></li>
                    <li>结算</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<section class="shop-checkout section">
    <div class="container">
        <?php if ($flash): ?>
            <div class="alert" style="padding:12px 16px;background:#fdecea;color:#c0392b;border-left:4px solid #e74c3c;margin-bottom:20px;border-radius:6px;">
                <?= htmlspecialchars($flash) ?>
            </div>
        <?php endif; ?>

        <form action="/actions/order_create.php" method="post">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">

            <div class="row">
                <div class="col-lg-7 col-12">
                    <div class="checkout-form-wrapper" style="background:#fff;padding:24px;border-radius:8px;border:1px solid #e6e8ed;box-shadow:0 1px 3px rgba(0,0,0,.04);">
                        <h3 style="color:#081828;margin-bottom:20px;">收货信息</h3>

                        <?php if ($addresses): ?>
                        <div style="margin-bottom:20px;">
                            <label style="color:#888;font-size:13px;">使用已保存地址</label>
                            <select name="address_id" id="address-select" class="form-control" style="background:#fff;border:1px solid #d9dce3;color:#081828;">
                                <option value="0">— 新填写地址 —</option>
                                <?php foreach ($addresses as $a): ?>
                                    <option value="<?= (int)$a['id'] ?>" <?= $a['is_default']?'selected':'' ?>
                                            data-receiver="<?= htmlspecialchars($a['receiver']) ?>"
                                            data-phone="<?= htmlspecialchars($a['phone']) ?>"
                                            data-province="<?= htmlspecialchars($a['province']) ?>"
                                            data-city="<?= htmlspecialchars($a['city']) ?>"
                                            data-district="<?= htmlspecialchars($a['district'] ?? '') ?>"
                                            data-detail="<?= htmlspecialchars($a['detail']) ?>">
                                        <?= htmlspecialchars($a['receiver']) ?> <?= htmlspecialchars($a['phone']) ?> -
                                        <?= htmlspecialchars($a['province'].$a['city'].($a['district']??'').$a['detail']) ?>
                                        <?= $a['is_default']?'（默认）':'' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-6"><div class="form-group"><label>收件人 *</label>
                                <input type="text" name="receiver" required maxlength="50" class="form-control" style="background:#fff;border:1px solid #d9dce3;color:#081828;"></div></div>
                            <div class="col-md-6"><div class="form-group"><label>手机号 *</label>
                                <input type="text" name="phone" required maxlength="20" class="form-control" style="background:#fff;border:1px solid #d9dce3;color:#081828;"></div></div>
                        </div>
                        <div class="row">
                            <div class="col-md-4"><div class="form-group"><label>省 *</label>
                                <input type="text" name="province" required maxlength="40" class="form-control" style="background:#fff;border:1px solid #d9dce3;color:#081828;"></div></div>
                            <div class="col-md-4"><div class="form-group"><label>市 *</label>
                                <input type="text" name="city" required maxlength="40" class="form-control" style="background:#fff;border:1px solid #d9dce3;color:#081828;"></div></div>
                            <div class="col-md-4"><div class="form-group"><label>区/县</label>
                                <input type="text" name="district" maxlength="40" class="form-control" style="background:#fff;border:1px solid #d9dce3;color:#081828;"></div></div>
                        </div>
                        <div class="form-group"><label>详细地址 *</label>
                            <input type="text" name="detail" required maxlength="255" class="form-control" style="background:#fff;border:1px solid #d9dce3;color:#081828;"></div>

                        <div class="form-group">
                            <label><input type="checkbox" name="save_address" value="1"> 保存为我的地址</label>
                        </div>
                        <div class="form-group"><label>订单备注</label>
                            <textarea name="remark" maxlength="255" rows="2" class="form-control" style="background:#fff;border:1px solid #d9dce3;color:#081828;"></textarea></div>
                    </div>
                </div>

                <div class="col-lg-5 col-12">
                    <div class="order-summary" style="background:#fff;padding:24px;border-radius:8px;border:1px solid #e6e8ed;box-shadow:0 1px 3px rgba(0,0,0,.04);">
                        <h3 style="color:#081828;margin-bottom:20px;">订单概览</h3>
                        <ul style="list-style:none;padding:0;margin:0;">
                            <?php foreach ($items as $it): ?>
                            <li style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #f0f2f5;color:#081828;">
                                <span><?= htmlspecialchars($it['name']) ?> × <?= (int)$it['effective_qty'] ?></span>
                                <span>￥<?= shop_fmt_price((int)$it['subtotal']) ?></span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <div style="padding-top:16px;display:flex;justify-content:space-between;color:#888;">
                            <span>件数</span><span><?= $total_count ?> 件</span>
                        </div>
                        <div style="padding-top:8px;display:flex;justify-content:space-between;font-size:20px;">
                            <span style="color:#081828;">应付</span>
                            <span style="color:#0167F3;font-weight:bold;">￥<?= shop_fmt_price($total_cents) ?></span>
                        </div>
                        <button type="submit" class="btn" style="width:100%;margin-top:20px;background:#0167F3;color:#fff;font-weight:bold;border-color:#0167F3;">
                            提交订单
                        </button>
                        <p style="margin-top:12px;font-size:12px;color:#888;text-align:center;">
                            注：当前未接入在线支付，提交订单后店家会联系你确认付款方式。
                        </p>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function(){
    var sel = document.getElementById('address-select');
    if (!sel) return;
    var form = sel.closest('form');
    function fill(option) {
        var fields = ['receiver','phone','province','city','district','detail'];
        fields.forEach(function(f){
            var el = form.querySelector('[name="'+f+'"]');
            if (el) el.value = option.dataset[f] || '';
        });
    }
    sel.addEventListener('change', function(){
        var opt = sel.options[sel.selectedIndex];
        if (sel.value === '0') {
            ['receiver','phone','province','city','district','detail'].forEach(function(f){
                var el = form.querySelector('[name="'+f+'"]');
                if (el) el.value = '';
            });
        } else {
            fill(opt);
        }
    });
    // 初始触发
    if (sel.value !== '0') fill(sel.options[sel.selectedIndex]);
});
</script>

<?php include __DIR__ . '/includes/footer_html.php'; ?>
