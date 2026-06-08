<?php
/**
 * 一次性种子脚本：插入示例商品数据
 * 用法：浏览器访问 https://paperchemis.top/install_seed.php（仅 owner 可见）
 * 幂等：按商品名判重，已存在则跳过。
 * 装完可删除本文件。
 */
require_once __DIR__ . '/includes/shop_bootstrap.php';
shop_require_login();  // 未登录走 SSO

if (($_SESSION['role'] ?? '') !== 'owner') {
    http_response_code(403);
    echo '仅 owner 可运行种子脚本。';
    exit;
}

// 取分类 id 映射（依赖 shop_db.php 的默认种子分类）
$cat_id = [];
$r = $conn->query("SELECT id, slug FROM shop_categories");
while ($row = $r->fetch_assoc()) $cat_id[$row['slug']] = (int)$row['id'];

$seeds = [
    [
        'name' => '纸片化学 · 本体（第三版）',
        'cat'  => 'tabletop',
        'subtitle' => '化学桌游本体盒装 · 含规则书与示范局',
        'description' => "包含：\n· 基础元素牌 110 张\n· 官能团牌 60 张\n· 反应牌 30 张\n· 规则书 1 本\n· 示范局指引 1 张\n\n2-5 人可玩，单局约 30-45 分钟。",
        'price_cents' => 6000, 'original_price_cents' => 8800,
        'stock' => 50, 'is_featured' => 1,
    ],
    [
        'name' => '纸片化学 · DLC 扩展包',
        'cat'  => 'cards',
        'subtitle' => '高阶官能团 + 聚合反应扩展',
        'description' => "新增 80 张扩展牌，覆盖：\n· 高阶官能团（醛、酯、酰胺…）\n· 聚合 / 缩合反应\n· 限定情景卡\n\n需配合本体使用。",
        'price_cents' => 4500, 'original_price_cents' => null,
        'stock' => 80, 'is_featured' => 1,
    ],
    [
        'name' => '纸片化学 · 24 冬季周边盲盒',
        'cat'  => 'goods',
        'subtitle' => '徽章 / 贴纸 / 印章随机组合',
        'description' => "限量周边盲盒，含：\n· 元素徽章 2 枚（随机）\n· 反应贴纸 4 张\n· 化学印章 1 枚\n\n每盒款式随机，不接受指定。",
        'price_cents' => 3500, 'original_price_cents' => 4900,
        'stock' => 30, 'is_featured' => 1,
    ],
    [
        'name' => '纸片化学 · 基础元素牌组',
        'cat'  => 'cards',
        'subtitle' => '单独补充装 / 收藏用',
        'description' => "本体内已包含该牌组，本品为独立补充装，适合损坏后补购或额外收藏。",
        'price_cents' => 2000, 'original_price_cents' => null,
        'stock' => 100, 'is_featured' => 0,
    ],
    [
        'name' => '纸片化学 · 课堂教学套装（10 人份）',
        'cat'  => 'edu',
        'subtitle' => '面向中学化学课堂 / 兴趣小组',
        'description' => "包含 5 套本体 + 教师指引 + 知识点对应表，可容纳 10-25 名学生同时进行。\n\n附赠：\n· 教师操作手册 1 本\n· 知识点对照表（按人教版章节编排）",
        'price_cents' => 28000, 'original_price_cents' => 35000,
        'stock' => 10, 'is_featured' => 1,
    ],
    [
        'name' => '纸片化学 · 收纳盒',
        'cat'  => 'goods',
        'subtitle' => '原木风，可装下本体 + DLC',
        'description' => "原木材质，桦木夹板，雕刻 logo。内有分隔，能同时收纳本体牌组 + DLC + 配件。",
        'price_cents' => 5500, 'original_price_cents' => null,
        'stock' => 20, 'is_featured' => 0,
    ],
];

$st_check = $conn->prepare("SELECT id FROM shop_products WHERE name=? LIMIT 1");
$st_ins = $conn->prepare("INSERT INTO shop_products
    (category_id, name, subtitle, description, price_cents, original_price_cents, stock, is_active, is_featured)
    VALUES (?,?,?,?,?,?,?,1,?)");

$inserted = []; $skipped = [];
foreach ($seeds as $s) {
    $st_check->bind_param('s', $s['name']);
    $st_check->execute();
    if ($st_check->get_result()->fetch_assoc()) { $skipped[] = $s['name']; continue; }

    $cid = $cat_id[$s['cat']] ?? null;
    $orig = $s['original_price_cents'];
    $st_ins->bind_param('isssiiis',
        $cid, $s['name'], $s['subtitle'], $s['description'],
        $s['price_cents'], $orig, $s['stock'], $s['is_featured']);
    $st_ins->execute();
    $inserted[] = $s['name'] . ' (#' . $conn->insert_id . ')';
}
$st_check->close();
$st_ins->close();

header('Content-Type: text/html; charset=utf-8');
echo '<!doctype html><meta charset="utf-8"><title>种子完成</title>';
echo '<body style="background:#f6f7fb;color:#081828;font-family:\'Roboto\',\'Microsoft YaHei\',sans-serif;padding:30px;line-height:1.8;max-width:800px;margin:30px auto;">';
echo '<h2 style="color:#0167F3;">✓ 种子脚本执行完成</h2>';
echo '<h3>新增 (' . count($inserted) . ')</h3><ul>';
foreach ($inserted as $n) echo '<li style="color:#0167F3;">+ ' . htmlspecialchars($n) . '</li>';
if (!$inserted) echo '<li style="color:#888;">无（已全部存在）</li>';
echo '</ul>';
echo '<h3>跳过 (' . count($skipped) . ')</h3><ul>';
foreach ($skipped as $n) echo '<li style="color:#888;">· ' . htmlspecialchars($n) . '</li>';
echo '</ul>';
echo '<p style="margin-top:30px;color:#e67e22;background:#fff7eb;padding:12px 16px;border-left:4px solid #e67e22;border-radius:6px;">⚠ 装完请删除本文件 /install_seed.php 防止被再次访问。</p>';
echo '<p><a href="/index.php" style="color:#0167F3;">→ 去首页看看</a> · <a href="/admin/products.php" style="color:#0167F3;">→ 后台商品列表</a></p>';
