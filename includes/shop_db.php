<?php
// shop 模块的建表脚本（懒建模式，与论坛主站风格一致）
// 调用方：每个 shop 入口在 require config.php 之后调用 shop_ensure_tables($conn)
//
// 表清单：
//   shop_categories       商品分类
//   shop_products         商品主表
//   shop_product_images   商品图片（一对多）
//   shop_cart_items       购物车（按 user_id 持久化）
//   shop_addresses        收货地址（一对多，可设默认）
//   shop_orders           订单主表
//   shop_order_items      订单项（下单时快照商品名/价）

if (!function_exists('shop_ensure_tables')) {
function shop_ensure_tables(mysqli $conn): void {
    static $done = false;
    if ($done) return;

    // 用户镜像表：每次 SSO 登录时由 callback.php 更新
    // id 主键 = 论坛端 users.id，**不自增**
    $conn->query("CREATE TABLE IF NOT EXISTS `users` (
        `id` int(11) NOT NULL,
        `mid` char(8) DEFAULT NULL,
        `username` varchar(50) NOT NULL,
        `email` varchar(100) DEFAULT NULL,
        `role` varchar(30) NOT NULL DEFAULT 'user',
        `avatar` varchar(255) DEFAULT NULL,
        `avatar_url` varchar(500) DEFAULT NULL,
        `points` int(11) NOT NULL DEFAULT 0,
        `exp` int(11) NOT NULL DEFAULT 0,
        `level` int(11) NOT NULL DEFAULT 1,
        `first_seen_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `last_sync_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_username` (`username`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $conn->query("CREATE TABLE IF NOT EXISTS `shop_categories` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(60) NOT NULL,
        `slug` varchar(60) NOT NULL,
        `sort_order` int(11) NOT NULL DEFAULT 0,
        `is_active` tinyint(1) NOT NULL DEFAULT 1,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_slug` (`slug`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $conn->query("CREATE TABLE IF NOT EXISTS `shop_products` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `category_id` int(11) DEFAULT NULL,
        `name` varchar(120) NOT NULL,
        `subtitle` varchar(200) DEFAULT NULL,
        `description` mediumtext,
        `price_cents` int(11) NOT NULL DEFAULT 0,
        `original_price_cents` int(11) DEFAULT NULL,
        `stock` int(11) NOT NULL DEFAULT 0,
        `cover` varchar(255) DEFAULT NULL,
        `sales_count` int(11) NOT NULL DEFAULT 0,
        `is_featured` tinyint(1) NOT NULL DEFAULT 0,
        `is_active` tinyint(1) NOT NULL DEFAULT 1,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_category` (`category_id`),
        KEY `idx_active_featured` (`is_active`, `is_featured`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $conn->query("CREATE TABLE IF NOT EXISTS `shop_product_images` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `product_id` int(11) NOT NULL,
        `url` varchar(255) NOT NULL,
        `sort_order` int(11) NOT NULL DEFAULT 0,
        PRIMARY KEY (`id`),
        KEY `idx_product` (`product_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $conn->query("CREATE TABLE IF NOT EXISTS `shop_cart_items` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `product_id` int(11) NOT NULL,
        `quantity` int(11) NOT NULL DEFAULT 1,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_user_product` (`user_id`, `product_id`),
        KEY `idx_user` (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $conn->query("CREATE TABLE IF NOT EXISTS `shop_addresses` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `receiver` varchar(50) NOT NULL,
        `phone` varchar(20) NOT NULL,
        `province` varchar(40) NOT NULL,
        `city` varchar(40) NOT NULL,
        `district` varchar(40) DEFAULT NULL,
        `detail` varchar(255) NOT NULL,
        `is_default` tinyint(1) NOT NULL DEFAULT 0,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_user` (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $conn->query("CREATE TABLE IF NOT EXISTS `shop_orders` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `order_no` varchar(32) NOT NULL,
        `user_id` int(11) NOT NULL,
        `total_cents` int(11) NOT NULL DEFAULT 0,
        `item_count` int(11) NOT NULL DEFAULT 0,
        `status` varchar(20) NOT NULL DEFAULT 'pending_payment',
        `receiver` varchar(50) NOT NULL,
        `phone` varchar(20) NOT NULL,
        `address_snapshot` varchar(500) NOT NULL,
        `remark` varchar(255) DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `paid_at` datetime DEFAULT NULL,
        `shipped_at` datetime DEFAULT NULL,
        `completed_at` datetime DEFAULT NULL,
        `cancelled_at` datetime DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_order_no` (`order_no`),
        KEY `idx_user_status` (`user_id`, `status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $conn->query("CREATE TABLE IF NOT EXISTS `shop_order_items` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `order_id` int(11) NOT NULL,
        `product_id` int(11) NOT NULL,
        `product_name` varchar(120) NOT NULL,
        `product_cover` varchar(255) DEFAULT NULL,
        `unit_price_cents` int(11) NOT NULL DEFAULT 0,
        `quantity` int(11) NOT NULL DEFAULT 1,
        `subtotal_cents` int(11) NOT NULL DEFAULT 0,
        PRIMARY KEY (`id`),
        KEY `idx_order` (`order_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // 种子分类（仅在 shop_categories 为空时插入，可在后台改）
    $cnt_res = $conn->query("SELECT COUNT(*) AS c FROM shop_categories");
    if ($cnt_res && (int)$cnt_res->fetch_assoc()['c'] === 0) {
        $seeds = [
            ['卡牌包',   'cards',     10],
            ['桌游本体', 'tabletop',  20],
            ['周边玩具', 'goods',     30],
            ['教学套装', 'edu',       40],
        ];
        $stmt = $conn->prepare("INSERT INTO shop_categories(name,slug,sort_order) VALUES(?,?,?)");
        foreach ($seeds as $s) {
            $stmt->bind_param('ssi', $s[0], $s[1], $s[2]);
            $stmt->execute();
        }
        $stmt->close();
    }

    $done = true;
}}

/** 价格分 → 元字符串（保留两位） */
if (!function_exists('shop_fmt_price')) {
function shop_fmt_price(int $cents): string {
    return number_format($cents / 100, 2);
}}

/** 生成订单号：年月日 + 6 位用户 id 后缀 + 4 位随机 */
if (!function_exists('shop_gen_order_no')) {
function shop_gen_order_no(int $user_id): string {
    return date('YmdHis') . str_pad((string)($user_id % 1000000), 6, '0', STR_PAD_LEFT) . substr(str_pad((string)random_int(0, 9999), 4, '0', STR_PAD_LEFT), 0, 4);
}}
