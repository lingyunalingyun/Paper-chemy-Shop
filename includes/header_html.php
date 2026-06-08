<?php
// Shop 公共头部 HTML 片段。由各 .php 页面在 require shop_bootstrap.php 之后 include。
//
// 期望调用方设置：
//   $page_title  当前页面 <title>
//   $active_nav  当前导航高亮 key：home | shop | cart | about | contact

if (!isset($page_title)) $page_title = '纸片化学 - 与化学相关的知识都在这里';
if (!isset($active_nav)) $active_nav = '';

$nav_categories = [];
$cat_res = $conn->query("SELECT id, name, slug FROM shop_categories WHERE is_active=1 ORDER BY sort_order, id");
if ($cat_res) { while ($r = $cat_res->fetch_assoc()) $nav_categories[] = $r; }

$header_cart_count = shop_cart_count($conn);
$header_user = shop_current_user($conn);
?>
<!DOCTYPE html>
<html class="no-js" lang="zh-CN">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <title><?= htmlspecialchars($page_title) ?></title>
    <meta name="description" content="纸片化学 - 桌游周边、卡牌包与教学套装" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="shortcut icon" type="image/x-icon" href="/assets/images/favicon.svg" />
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="/assets/css/LineIcons.3.0.css" />
    <link rel="stylesheet" href="/assets/css/tiny-slider.css" />
    <link rel="stylesheet" href="/assets/css/glightbox.min.css" />
    <link rel="stylesheet" href="/assets/css/main.css" />
    <style>
        /* 商品卡片缩略图：等高裁切，避免大图撑变形 */
        .single-product .product-image {
            height: 240px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fafbfd;
        }
        .single-product .product-image img {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover;
        }
        /* 详情页主图容器最大高度（详情页 inline 已限，这里兜底） */
        .product-images .main-img img { max-width: 100%; height: auto; }
    </style>
</head>
<body>
    <div class="preloader">
        <div class="preloader-inner">
            <div class="preloader-icon"><span></span><span></span></div>
        </div>
    </div>

    <header class="header navbar-area">
        <div class="topbar">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-4 col-md-4 col-12">
                        <div class="top-middle">
                            <ul class="useful-links">
                                <li><a href="/index.php">主页</a></li>
                                <li><a href="/about-us.php">关于我们</a></li>
                                <li><a href="/contact.php">联系我们</a></li>
                                <li><a href="https://musetreehouse.com/index.php" target="_blank">返回论坛</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4 col-12 ms-auto">
                        <div class="top-end">
                            <?php if ($header_user): ?>
                                <div class="user">
                                    <i class="lni lni-user"></i>
                                    你好，<?= htmlspecialchars($header_user['username']) ?>
                                </div>
                                <ul class="user-login">
                                    <?php if (in_array($header_user['role'] ?? '', ['owner','admin'], true)): ?>
                                        <li><a href="/admin/index.php" style="color:#ffd166;"><i class="lni lni-cog"></i> 后台</a></li>
                                    <?php endif; ?>
                                    <li><a href="/orders.php">我的订单</a></li>
                                    <li><a href="/sso/logout.php">退出</a></li>
                                </ul>
                            <?php else: ?>
                                <div class="user">
                                    <i class="lni lni-user"></i>
                                    你好
                                </div>
                                <ul class="user-login">
                                    <li><a href="/sso/start.php?back=<?= urlencode($_SERVER['REQUEST_URI'] ?? '/') ?>">登录</a></li>
                                    <li><a href="https://musetreehouse.com/pages/register.php" target="_blank">注册</a></li>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="header-middle">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-3 col-md-3 col-7">
                        <a class="navbar-brand" href="/index.php">
                            <img src="/assets/images/logo/logo.svg" alt="纸片化学">
                        </a>
                    </div>
                    <div class="col-lg-5 col-md-7 d-xs-none">
                        <div class="main-menu-search">
                            <div class="navbar-search search-style-5">
                                <form action="/product-grids.php" method="get">
                                    <div class="search-input">
                                        <input type="text" name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" placeholder="搜索商品">
                                    </div>
                                    <div class="search-btn">
                                        <button type="submit"><i class="lni lni-search-alt"></i></button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-2 col-5">
                        <div class="middle-right-area" style="justify-content:flex-end;gap:30px;">
                            <div class="nav-hotline">
                                <i class="lni lni-comments-alt"></i>
                                <h3>客服：<span>论坛站内私信</span></h3>
                            </div>
                            <div class="navbar-cart">
                                <?php if ($header_user): ?>
                                <div class="wishlist">
                                    <a href="/orders.php" title="我的订单">
                                        <i class="lni lni-package"></i>
                                    </a>
                                </div>
                                <?php endif; ?>
                                <div class="cart-items">
                                    <a href="/cart.php" class="main-btn">
                                        <i class="lni lni-cart"></i>
                                        <span class="total-items"><?= $header_cart_count ?></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8 col-md-6 col-12">
                    <div class="nav-inner">
                        <div class="mega-category-menu">
                            <span class="cat-button"><i class="lni lni-menu"></i>所有类别</span>
                            <ul class="sub-category">
                                <?php foreach ($nav_categories as $cat): ?>
                                    <li><a href="/product-grids.php?cat=<?= (int)$cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></a></li>
                                <?php endforeach; ?>
                                <?php if (!$nav_categories): ?>
                                    <li><a href="/product-grids.php">暂无分类</a></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                        <nav class="navbar navbar-expand-lg">
                            <button class="navbar-toggler mobile-menu-btn" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
                                <span class="toggler-icon"></span>
                                <span class="toggler-icon"></span>
                                <span class="toggler-icon"></span>
                            </button>
                            <div class="collapse navbar-collapse sub-menu-bar" id="navbarSupportedContent">
                                <ul id="nav" class="navbar-nav ms-auto">
                                    <li class="nav-item"><a href="/index.php" class="<?= $active_nav==='home'?'active':'' ?>">主页</a></li>
                                    <li class="nav-item"><a href="/product-grids.php" class="<?= $active_nav==='shop'?'active':'' ?>">全部商品</a></li>
                                    <li class="nav-item"><a href="/cart.php" class="<?= $active_nav==='cart'?'active':'' ?>">购物车</a></li>
                                    <li class="nav-item"><a href="/about-us.php" class="<?= $active_nav==='about'?'active':'' ?>">关于</a></li>
                                    <li class="nav-item"><a href="/contact.php" class="<?= $active_nav==='contact'?'active':'' ?>">联系</a></li>
                                </ul>
                            </div>
                        </nav>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-12">
                    <div class="nav-social">
                        <h5 class="title">跨站入口：</h5>
                        <ul>
                            <li><a href="https://musetreehouse.com/index.php" title="缪斯树洞论坛" target="_blank"><i class="lni lni-home"></i></a></li>
                            <li><a href="https://musetreehouse.com/square.php" title="论坛广场" target="_blank"><i class="lni lni-comments"></i></a></li>
                            <li><a href="https://musetreehouse.com/sheets.php" title="光遇曲库" target="_blank"><i class="lni lni-music"></i></a></li>
                            <li><a href="https://musetreehouse.com/bartender.php" title="调酒" target="_blank"><i class="lni lni-mushroom"></i></a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </header>
