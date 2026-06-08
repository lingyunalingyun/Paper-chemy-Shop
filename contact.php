<?php
require_once __DIR__ . '/includes/shop_bootstrap.php';
$page_title = '联系我们 - 纸片化学';
$active_nav = 'contact';
include __DIR__ . '/includes/header_html.php';
?>
<div class="breadcrumbs">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 col-md-6 col-12"><div class="breadcrumbs-content"><h1 class="page-title">联系我们</h1></div></div>
            <div class="col-lg-6 col-md-6 col-12"><ul class="breadcrumb-nav">
                <li><a href="/index.php"><i class="lni lni-home"></i> 主页</a></li>
                <li>联系我们</li>
            </ul></div>
        </div>
    </div>
</div>

<section class="contact-us section">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 col-12 offset-lg-2">
                <div style="background:#fff;border:1px solid #e6e8ed;border-radius:8px;padding:40px;color:#081828;line-height:1.9;box-shadow:0 1px 3px rgba(0,0,0,.04);">
                    <h2 style="color:#0167F3;">怎么联系到我们</h2>
                    <p>主机不支持发送邮件，所有沟通走<strong>缪斯树洞论坛</strong>。请用以下方式留言：</p>

                    <ul>
                        <li><i class="lni lni-comments"></i> <a href="https://musetreehouse.com/contact.php" target="_blank">论坛站内表单</a> — 留下你的问题，我们会回复</li>
                        <li><i class="lni lni-envelope"></i> 论坛私信：登录后通过 <a href="https://musetreehouse.com/messages.php" target="_blank">站内私信</a> 找站长</li>
                        <li><i class="lni lni-question-circle"></i> <a href="/about-us.php">关于纸片化学</a> — 了解本商店与桌游本身</li>
                    </ul>

                    <h3 style="color:#081828;margin-top:30px;">客服时段</h3>
                    <p style="color:#888;">周一至周五 19:00 - 23:00（其他时段也会陆续回复）</p>

                    <h3 style="color:#081828;margin-top:30px;">订单/退换</h3>
                    <p>下单后请前往 <a href="/orders.php">我的订单</a> 查看状态。若需修改 / 取消订单，论坛私信告知订单号即可。</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer_html.php'; ?>
