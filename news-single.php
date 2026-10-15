<?php
declare(strict_types=1);
require __DIR__ . '/includes/init.php';

$slug = trim((string) request('slug', ''));
$item = $slug !== ''
    ? db_query('SELECT * FROM news WHERE slug = ? AND published_at IS NOT NULL AND published_at <= NOW()', [$slug])->fetch()
    : null;

if (!$item) {
    http_response_code(404);
    $meta = page_meta(t('err.not_found'));
    require __DIR__ . '/includes/header.php';
    echo '<section class="section"><div class="container"><h1>' . e(t('err.not_found')) . '</h1></div></section>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$meta = page_meta($item['title'], excerpt($item['body'], 160));
require __DIR__ . '/includes/header.php';
?>
<article class="section">
  <div class="container prose">
    <p class="kicker"><a href="<?= e(url('news.php')) ?>"><?= e(t('news.title')) ?></a></p>
    <h1><?= e($item['title']) ?></h1>
    <p class="muted"><?= e($item['category']) ?> · <?= e(t('news.published')) ?> <?= e(bn_date($item['published_at'])) ?></p>
    <?= nl2p($item['body']) ?>
  </div>
</article>
<?php require __DIR__ . '/includes/footer.php'; ?>
