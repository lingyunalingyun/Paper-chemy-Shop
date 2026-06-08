<?php
require_once __DIR__ . '/includes/shop_bootstrap.php';
$page_title = '关于纸片化学';
$active_nav = 'about';
include __DIR__ . '/includes/header_html.php';
?>
<div class="breadcrumbs">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 col-md-6 col-12"><div class="breadcrumbs-content"><h1 class="page-title">关于纸片化学</h1></div></div>
            <div class="col-lg-6 col-md-6 col-12"><ul class="breadcrumb-nav">
                <li><a href="/index.php"><i class="lni lni-home"></i> 主页</a></li>
                <li>关于我们</li>
            </ul></div>
        </div>
    </div>
</div>

<section class="about-us section">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 col-12 offset-lg-2">
                <div style="background:#fff;border:1px solid #e6e8ed;border-radius:8px;padding:40px;color:#081828;line-height:1.9;box-shadow:0 1px 3px rgba(0,0,0,.04);">
                    <h2 style="color:#0167F3;">纸片化学 PaperChem</h2>
                    <p>一款桌面上的化学桌游 —— 用一叠卡牌还原化学反应、官能团、聚合 …… 适合 <strong>科普启蒙</strong>、<strong>课堂教学</strong> 与 <strong>朋友聚会</strong>。</p>
                    <p>本商店是纸片化学桌游的官方周边小铺，目前在售：</p>
                    <ul>
                        <li><strong>卡牌包</strong>：基础元素牌 / 官能团扩展 / 反应限定 …</li>
                        <li><strong>桌游本体</strong>：第三版盒装，含规则书与示范局</li>
                        <li><strong>周边玩具</strong>：贴纸、徽章、印章、收纳盒</li>
                        <li><strong>教学套装</strong>：面向中学化学课堂的批量装</li>
                    </ul>

                    <h3 style="color:#081828;margin-top:30px;">如何下单</h3>
                    <p>1. 用 <a href="/sso/start.php">缪斯树洞论坛账号</a> 登录（同一套账号，没账号可<a href="https://musetreehouse.com/pages/register.php" target="_blank">免费注册</a>）。<br>
                       2. 加入购物车 → 填写收货信息 → 提交订单。<br>
                       3. 提交订单后，店家会在论坛私信你确认付款方式（暂未接入在线支付）。</p>

                    <p style="margin-top:30px;color:#888;font-size:13px;">本商店由 <a href="https://musetreehouse.com/index.php" target="_blank">缪斯树洞论坛</a> 承载，账号系统通过 SSO 与论坛打通。</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer_html.php'; ?>
