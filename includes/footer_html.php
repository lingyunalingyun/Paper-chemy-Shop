    <section class="shipping-info">
        <div class="container">
            <ul>
                <li>
                    <div class="media-icon"><i class="lni lni-delivery"></i></div>
                    <div class="media-body">
                        <h5>满 99 元包邮</h5>
                        <span>大陆地区中通/圆通</span>
                    </div>
                </li>
                <li>
                    <div class="media-icon"><i class="lni lni-support"></i></div>
                    <div class="media-body">
                        <h5>桌游客服</h5>
                        <span>论坛站内私信即可</span>
                    </div>
                </li>
                <li>
                    <div class="media-icon"><i class="lni lni-credit-cards"></i></div>
                    <div class="media-body">
                        <h5>下单后手动核价</h5>
                        <span>暂以线下/收款码结算</span>
                    </div>
                </li>
                <li>
                    <div class="media-icon"><i class="lni lni-reload"></i></div>
                    <div class="media-body">
                        <h5>7 天无理由</h5>
                        <span>未拆封支持退换</span>
                    </div>
                </li>
            </ul>
        </div>
    </section>

    <footer class="footer">
        <div class="footer-top">
            <div class="container">
                <div class="inner-content">
                    <div class="row">
                        <div class="col-lg-3 col-md-4 col-12">
                            <div class="footer-logo">
                                <a href="/index.php"><img src="/assets/images/logo/white-logo.svg" alt="纸片化学"></a>
                            </div>
                        </div>
                        <div class="col-lg-9 col-md-8 col-12">
                            <div class="footer-newsletter">
                                <h4 class="title">
                                    纸片化学 — 一款桌面化学桌游
                                    <span>购买卡牌包 / 桌游本体 / 周边玩具 / 教学套装</span>
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-middle">
            <div class="container">
                <div class="bottom-inner">
                    <div class="row">
                        <div class="col-lg-4 col-md-6 col-12">
                            <div class="single-footer f-contact">
                                <h3>联系我们</h3>
                                <p class="mail"><a href="https://musetreehouse.com/contact.php" target="_blank">在论坛留言</a></p>
                                <ul>
                                    <li>客服时段：周一至周五 19:00 - 23:00</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 col-12">
                            <div class="single-footer f-link">
                                <h3>信息</h3>
                                <ul>
                                    <li><a href="/about-us.php">关于纸片化学</a></li>
                                    <li><a href="/contact.php">联系我们</a></li>
                                    <li><a href="https://musetreehouse.com/index.php" target="_blank">缪斯树洞论坛</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 col-12">
                            <div class="single-footer f-link">
                                <h3>商品分类</h3>
                                <ul>
                                    <?php foreach ($nav_categories as $cat): ?>
                                        <li><a href="/product-grids.php?cat=<?= (int)$cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="container">
                <div class="inner-content">
                    <div class="row align-items-center">
                        <div class="col-lg-12 col-12 text-center">
                            <span>&copy; <?= date('Y') ?> 纸片化学 · 由 <a href="https://musetreehouse.com/index.php" target="_blank" style="color:#0167F3;">缪斯树洞</a> 承载</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <a href="#" class="scroll-top"><i class="lni lni-chevron-up"></i></a>

    <script src="/assets/js/bootstrap.min.js"></script>
    <script src="/assets/js/tiny-slider.js"></script>
    <script src="/assets/js/glightbox.min.js"></script>
    <script src="/assets/js/main.js"></script>
    <?php if (!empty($enable_hero_slider)): ?>
    <script>
        tns({
            container: '.hero-slider',
            slideBy: 'page', autoplay: true, autoplayButtonOutput: false,
            mouseDrag: true, gutter: 0, items: 1, nav: false, controls: true,
            controlsText: ['<i class="lni lni-chevron-left"></i>', '<i class="lni lni-chevron-right"></i>'],
        });
    </script>
    <?php endif; ?>
</body>
</html>
