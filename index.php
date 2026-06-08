<?php
require_once __DIR__ . '/includes/shop_bootstrap.php';

$page_title = '纸片化学 - 与化学相关的知识都在这里';
$active_nav = 'home';
$enable_hero_slider = true;

// 精选商品（is_featured=1 优先；不够则补普通在售）
$featured = [];
$res = $conn->query("SELECT id,name,subtitle,price_cents,original_price_cents,cover,sales_count
                     FROM shop_products
                     WHERE is_active=1
                     ORDER BY is_featured DESC, sales_count DESC, id DESC
                     LIMIT 8");
if ($res) { while ($r = $res->fetch_assoc()) $featured[] = $r; }

// 分类卡（首页第二个 section）
$cats_for_home = [];
$res = $conn->query("SELECT id,name,slug FROM shop_categories WHERE is_active=1 ORDER BY sort_order,id LIMIT 3");
if ($res) { while ($r = $res->fetch_assoc()) $cats_for_home[] = $r; }

include __DIR__ . '/includes/header_html.php';
?>

<section class="hero-area">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 col-12 custom-padding-right">
                <div class="slider-head">
                    <div class="hero-slider">
                        <div class="single-slider" style="background-image: url(/assets/images/hero/slider-bg1.jpg);">
                            <div class="content">
                                <h2><span>正在预售</span>纸片化学 - 本体（第三版）</h2>
                                <p>与朋友一起体验全新的化学世界，同时体验全新的玩法！</p>
                                <h3><span>现只需要</span> ￥60.00</h3>
                                <div class="button">
                                    <a href="/product-grids.php" class="btn">立即购买</a>
                                </div>
                            </div>
                        </div>
                        <div class="single-slider" style="background-image: url(/assets/images/hero/slider-bg2.jpg);">
                            <div class="content">
                                <h2><span>正在预售</span>纸片化学 - DLC</h2>
                                <p>超爽 DLC，购买后与好友一起畅享！（建议先购买并游玩本体后再购买）</p>
                                <h3><span>现只需要</span> ￥45.00</h3>
                                <div class="button">
                                    <a href="/product-grids.php" class="btn">立即购买</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-12">
                <div class="row">
                    <div class="col-lg-12 col-md-6 col-12 md-custom-padding">
                        <div class="hero-small-banner" style="background-image: url('/assets/images/hero/slider-bnr.jpg');">
                            <div class="content">
                                <h2><span>正在热卖</span>纸片化学 - 24 冬季周边</h2>
                                <h3>￥35.00</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12 col-md-6 col-12">
                        <div class="hero-small-banner style2">
                            <div class="content">
                                <h2>官方限定周免！</h2>
                                <p>可可爱爱，没人不爱。随身携带，魅力常在</p>
                                <div class="button">
                                    <a class="btn" href="/product-grids.php">立即购买！</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if ($cats_for_home): ?>
<section class="featured-categories section">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="section-title">
                    <h2>商品分类</h2>
                    <p>按类目浏览，找到你想要的纸片化学周边。</p>
                </div>
            </div>
        </div>
        <div class="row">
            <?php foreach ($cats_for_home as $cat): ?>
            <div class="col-lg-4 col-md-6 col-12">
                <div class="single-category">
                    <h3 class="heading"><?= htmlspecialchars($cat['name']) ?></h3>
                    <p>点击查看本类目下的全部商品。</p>
                    <ul>
                        <li><a href="/product-grids.php?cat=<?= (int)$cat['id'] ?>">查看详细</a></li>
                    </ul>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="trending-product section">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="section-title">
                    <h2>精选商品</h2>
                    <p>从所有上架商品中挑选最受欢迎的一批。</p>
                </div>
            </div>
        </div>
        <div class="row">
            <?php if (!$featured): ?>
                <div class="col-12">
                    <div class="alert" style="padding:30px;background:#fff;color:#081828;border:1px dashed #d9dce3;text-align:center;border-radius:6px;">
                        暂无上架商品。<?= ($header_user && in_array($header_user['role'], ['admin','owner'], true)) ? '<a href="/admin/products.php" style="color:#0167F3;">前往后台添加</a>' : '请等待店家上新～' ?>
                    </div>
                </div>
            <?php else: foreach ($featured as $p): ?>
                <div class="col-lg-3 col-md-6 col-12">
                    <div class="single-product">
                        <div class="product-image">
                            <img src="<?= htmlspecialchars($p['cover'] ?: '/assets/images/products/product-1.jpg') ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                            <div class="button">
                                <a href="/product-details.php?id=<?= (int)$p['id'] ?>" class="btn"><i class="lni lni-cart"></i> 查看商品</a>
                            </div>
                        </div>
                        <div class="product-info">
                            <h4 class="title">
                                <a href="/product-details.php?id=<?= (int)$p['id'] ?>"><?= htmlspecialchars($p['name']) ?></a>
                            </h4>
                            <?php if (!empty($p['subtitle'])): ?>
                                <span class="category"><?= htmlspecialchars($p['subtitle']) ?></span>
                            <?php endif; ?>
                            <div class="price">
                                <span>￥<?= shop_fmt_price((int)$p['price_cents']) ?></span>
                                <?php if (!empty($p['original_price_cents']) && $p['original_price_cents'] > $p['price_cents']): ?>
                                    <span class="discount-price">￥<?= shop_fmt_price((int)$p['original_price_cents']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer_html.php'; ?>
