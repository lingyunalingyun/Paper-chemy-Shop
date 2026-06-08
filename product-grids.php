<?php
require_once __DIR__ . '/includes/shop_bootstrap.php';

$page_title = '全部商品 - 纸片化学';
$active_nav = 'shop';

$cat_id = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;
$q      = trim((string)($_GET['q'] ?? ''));
$sort   = (string)($_GET['sort'] ?? 'hot');
$page   = max(1, (int)($_GET['page'] ?? 1));
$per    = 12;

$order_sql = "is_featured DESC, sales_count DESC, id DESC";
if ($sort === 'new')        $order_sql = "id DESC";
elseif ($sort === 'price_asc')  $order_sql = "price_cents ASC, id DESC";
elseif ($sort === 'price_desc') $order_sql = "price_cents DESC, id DESC";

$where = ["is_active=1"];
$bind  = [];
$types = '';
if ($cat_id > 0) { $where[] = "category_id=?"; $bind[] = $cat_id; $types .= 'i'; }
if ($q !== '')   { $where[] = "(name LIKE ? OR subtitle LIKE ?)"; $kw = '%'.$q.'%'; $bind[]=$kw; $bind[]=$kw; $types.='ss'; }
$where_sql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// 总数
$total = 0;
$cnt_sql = "SELECT COUNT(*) AS c FROM shop_products $where_sql";
$st = $conn->prepare($cnt_sql);
if ($bind) $st->bind_param($types, ...$bind);
$st->execute();
$total = (int)$st->get_result()->fetch_assoc()['c'];
$st->close();
$total_pages = max(1, (int)ceil($total / $per));
if ($page > $total_pages) $page = $total_pages;
$offset = ($page - 1) * $per;

// 数据
$products = [];
$sql = "SELECT id,name,subtitle,price_cents,original_price_cents,cover,sales_count
        FROM shop_products $where_sql ORDER BY $order_sql LIMIT ? OFFSET ?";
$st = $conn->prepare($sql);
$bind2 = $bind; $bind2[] = $per; $bind2[] = $offset;
$st->bind_param($types . 'ii', ...$bind2);
$st->execute();
$res = $st->get_result();
while ($r = $res->fetch_assoc()) $products[] = $r;
$st->close();

// 侧栏：分类带商品数
$sidebar_cats = [];
$cat_res = $conn->query("SELECT c.id, c.name,
                                (SELECT COUNT(*) FROM shop_products p WHERE p.category_id=c.id AND p.is_active=1) AS n
                         FROM shop_categories c WHERE c.is_active=1 ORDER BY c.sort_order, c.id");
if ($cat_res) { while ($r = $cat_res->fetch_assoc()) $sidebar_cats[] = $r; }

$current_cat_name = '';
if ($cat_id > 0) {
    foreach ($sidebar_cats as $c) { if ((int)$c['id'] === $cat_id) { $current_cat_name = $c['name']; break; } }
}

include __DIR__ . '/includes/header_html.php';

function shop_qs(array $override): string {
    $base = $_GET; foreach ($override as $k => $v) { if ($v === null) unset($base[$k]); else $base[$k] = $v; }
    return $base ? ('?' . http_build_query($base)) : '';
}
?>

<div class="breadcrumbs">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 col-md-6 col-12">
                <div class="breadcrumbs-content">
                    <h1 class="page-title"><?= htmlspecialchars($current_cat_name ?: ($q !== '' ? "搜索：$q" : '全部商品')) ?></h1>
                </div>
            </div>
            <div class="col-lg-6 col-md-6 col-12">
                <ul class="breadcrumb-nav">
                    <li><a href="/index.php"><i class="lni lni-home"></i> 主页</a></li>
                    <li>商品</li>
                    <?php if ($current_cat_name): ?><li><?= htmlspecialchars($current_cat_name) ?></li><?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<section class="product-grids section">
    <div class="container">
        <div class="row">
            <div class="col-lg-3 col-12">
                <div class="product-sidebar">
                    <div class="single-widget search">
                        <h3>搜索商品</h3>
                        <form action="/product-grids.php" method="get">
                            <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="输入关键字...">
                            <?php if ($cat_id) echo '<input type="hidden" name="cat" value="'.$cat_id.'">'; ?>
                            <button type="submit"><i class="lni lni-search-alt"></i></button>
                        </form>
                    </div>

                    <div class="single-widget">
                        <h3>商品分类</h3>
                        <ul class="list">
                            <li><a href="/product-grids.php<?= $q!==''?'?q='.urlencode($q):'' ?>">全部</a></li>
                            <?php foreach ($sidebar_cats as $c): ?>
                                <li>
                                    <a href="<?= '/product-grids.php' . shop_qs(['cat'=>(int)$c['id'], 'page'=>null]) ?>"
                                       <?= $cat_id === (int)$c['id'] ? 'style="color:#0167F3;font-weight:bold;"' : '' ?>>
                                       <?= htmlspecialchars($c['name']) ?></a><span>(<?= (int)$c['n'] ?>)</span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-lg-9 col-12">
                <div class="product-grids-head">
                    <div class="product-grid-topbar">
                        <div class="row align-items-center">
                            <div class="col-lg-7 col-md-8 col-12">
                                <div class="product-sorting">
                                    <label for="sorting">排序：</label>
                                    <select class="form-control" id="sorting" onchange="location.href=this.value">
                                        <?php
                                        $sorts = ['hot'=>'热度','new'=>'最新','price_asc'=>'价格 低→高','price_desc'=>'价格 高→低'];
                                        foreach ($sorts as $k=>$label):
                                            $url = '/product-grids.php' . shop_qs(['sort'=>$k, 'page'=>null]);
                                        ?>
                                            <option value="<?= htmlspecialchars($url) ?>" <?= $sort===$k?'selected':'' ?>><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <h3 class="total-show-product">共 <span><?= $total ?> 件</span></h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <?php if (!$products): ?>
                            <div class="col-12">
                                <div class="alert" style="padding:30px;background:#fff;color:#081828;border:1px dashed #d9dce3;text-align:center;border-radius:6px;">
                                    没有找到匹配的商品。<a href="/product-grids.php" style="color:#0167F3;">查看全部</a>
                                </div>
                            </div>
                        <?php else: foreach ($products as $p): ?>
                            <div class="col-lg-4 col-md-6 col-12">
                                <div class="single-product">
                                    <div class="product-image">
                                        <img src="<?= htmlspecialchars($p['cover'] ?: '/assets/images/products/product-1.jpg') ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                                        <?php if (!empty($p['original_price_cents']) && $p['original_price_cents'] > $p['price_cents']):
                                            $off = (int)round(100 * (1 - $p['price_cents'] / $p['original_price_cents'])); ?>
                                            <span class="sale-tag">-<?= $off ?>%</span>
                                        <?php endif; ?>
                                        <div class="button">
                                            <a href="/product-details.php?id=<?= (int)$p['id'] ?>" class="btn"><i class="lni lni-cart"></i> 查看详情</a>
                                        </div>
                                    </div>
                                    <div class="product-info">
                                        <?php if (!empty($p['subtitle'])): ?>
                                            <span class="category"><?= htmlspecialchars($p['subtitle']) ?></span>
                                        <?php endif; ?>
                                        <h4 class="title">
                                            <a href="/product-details.php?id=<?= (int)$p['id'] ?>"><?= htmlspecialchars($p['name']) ?></a>
                                        </h4>
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

                    <?php if ($total_pages > 1): ?>
                    <div class="pagination left">
                        <ul class="pagination-list">
                            <?php
                            $pn = function($n, $label = null) {
                                $url = '/product-grids.php' . shop_qs(['page' => $n]);
                                return '<li><a href="'.htmlspecialchars($url).'">'.($label ?? $n).'</a></li>';
                            };
                            if ($page > 1) echo $pn($page-1, '«');
                            for ($i = max(1, $page-2); $i <= min($total_pages, $page+2); $i++) {
                                if ($i === $page) echo '<li class="active"><a href="javascript:void(0)">'.$i.'</a></li>';
                                else echo $pn($i);
                            }
                            if ($page < $total_pages) echo $pn($page+1, '»');
                            ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer_html.php'; ?>
