<?php
require_once __DIR__ . '/includes/shop_bootstrap.php';

$pid = (int)($_GET['id'] ?? 0);
if ($pid <= 0) { header('Location: /product-grids.php'); exit; }

$stmt = $conn->prepare("SELECT p.*, c.name AS cat_name, c.id AS cat_id
                        FROM shop_products p
                        LEFT JOIN shop_categories c ON c.id = p.category_id
                        WHERE p.id=? AND p.is_active=1 LIMIT 1");
$stmt->bind_param('i', $pid);
$stmt->execute();
$prod = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$prod) {
    http_response_code(404);
    $page_title = '商品不存在';
    include __DIR__ . '/includes/header_html.php';
    echo '<section class="error-area section"><div class="container"><div style="padding:60px;text-align:center;color:#666;">商品不存在或已下架。<br><a href="/product-grids.php" style="color:#0167F3;">返回商品列表</a></div></div></section>';
    include __DIR__ . '/includes/footer_html.php';
    exit;
}

// 副图
$images = [];
$img_res = $conn->query("SELECT url FROM shop_product_images WHERE product_id=" . (int)$prod['id'] . " ORDER BY sort_order, id");
if ($img_res) { while ($r = $img_res->fetch_assoc()) $images[] = $r['url']; }
if (!$images && !empty($prod['cover'])) $images[] = $prod['cover'];

$cover = $images[0] ?? '/assets/images/products/product-1.jpg';

$page_title = $prod['name'] . ' - 纸片化学';
$active_nav = 'shop';

include __DIR__ . '/includes/header_html.php';
?>

<div class="breadcrumbs">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 col-md-6 col-12">
                <div class="breadcrumbs-content">
                    <h1 class="page-title"><?= htmlspecialchars($prod['name']) ?></h1>
                </div>
            </div>
            <div class="col-lg-6 col-md-6 col-12">
                <ul class="breadcrumb-nav">
                    <li><a href="/index.php"><i class="lni lni-home"></i> 主页</a></li>
                    <li><a href="/product-grids.php">商品</a></li>
                    <?php if (!empty($prod['cat_name'])): ?>
                        <li><a href="/product-grids.php?cat=<?= (int)$prod['cat_id'] ?>"><?= htmlspecialchars($prod['cat_name']) ?></a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<section class="item-details section">
    <div class="container">
        <div class="top-area">
            <div class="row align-items-center">
                <div class="col-lg-6 col-md-12 col-12">
                    <div class="product-images">
                        <main id="gallery">
                            <div class="main-img" style="display:flex;align-items:center;justify-content:center;background:#fafbfd;border:1px solid #eef0f3;border-radius:8px;max-height:500px;overflow:hidden;padding:20px;">
                                <img src="<?= htmlspecialchars($cover) ?>" id="current" alt="<?= htmlspecialchars($prod['name']) ?>" style="max-height:460px;max-width:100%;width:auto;height:auto;object-fit:contain;">
                            </div>
                            <?php if (count($images) > 1): ?>
                            <div class="images">
                                <?php foreach ($images as $u): ?>
                                    <img src="<?= htmlspecialchars($u) ?>" class="img" alt="">
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </main>
                    </div>
                </div>
                <div class="col-lg-6 col-md-12 col-12">
                    <div class="product-info">
                        <h2 class="title"><?= htmlspecialchars($prod['name']) ?></h2>
                        <?php if (!empty($prod['cat_name'])): ?>
                        <p class="category"><i class="lni lni-tag"></i>
                            <a href="/product-grids.php?cat=<?= (int)$prod['cat_id'] ?>"><?= htmlspecialchars($prod['cat_name']) ?></a>
                        </p>
                        <?php endif; ?>
                        <h3 class="price">
                            ￥<?= shop_fmt_price((int)$prod['price_cents']) ?>
                            <?php if (!empty($prod['original_price_cents']) && $prod['original_price_cents'] > $prod['price_cents']): ?>
                                <span>￥<?= shop_fmt_price((int)$prod['original_price_cents']) ?></span>
                            <?php endif; ?>
                        </h3>
                        <?php if (!empty($prod['subtitle'])): ?>
                            <p class="info-text"><?= htmlspecialchars($prod['subtitle']) ?></p>
                        <?php endif; ?>
                        <p style="color:#888;font-size:13px;">库存：<?= (int)$prod['stock'] ?> 件 · 已售 <?= (int)$prod['sales_count'] ?> 件</p>

                        <form action="/actions/cart_add.php" method="post" style="margin-top:20px;">
                            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                            <input type="hidden" name="product_id" value="<?= (int)$prod['id'] ?>">
                            <div class="row">
                                <div class="col-lg-4 col-md-4 col-12">
                                    <div class="form-group quantity">
                                        <label>数量</label>
                                        <input type="number" name="quantity" min="1" max="<?= max(1, (int)$prod['stock']) ?>" value="1" class="form-control" style="height:46px;">
                                    </div>
                                </div>
                            </div>
                            <div class="bottom-content">
                                <div class="row align-items-end">
                                    <div class="col-lg-6 col-md-6 col-12">
                                        <div class="button cart-button">
                                            <button type="submit" class="btn" style="width:100%;" <?= $prod['stock'] <= 0 ? 'disabled' : '' ?>>
                                                <i class="lni lni-cart"></i> <?= $prod['stock'] <= 0 ? '已售罄' : '加入购物车' ?>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-6 col-12">
                                        <div class="wish-button">
                                            <a href="/cart.php" class="btn" style="display:block;text-align:center;"><i class="lni lni-list"></i> 查看购物车</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($prod['description'])): ?>
        <div class="product-details-info">
            <div class="single-block">
                <h4>商品详情</h4>
                <div style="color:#081828;line-height:1.8;"><?= nl2br(htmlspecialchars($prod['description'])) ?></div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer_html.php'; ?>
