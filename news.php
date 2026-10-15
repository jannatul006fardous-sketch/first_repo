<?php
declare(strict_types=1);
require __DIR__ . '/includes/init.php';

$items = db_query(
    'SELECT * FROM news WHERE published_at IS NOT NULL AND published_at <= NOW() ORDER BY published_at DESC'
)->fetchAll();

$meta = page_meta(t('news.title'));
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
  <div class="container">
    <h1><?= e(t('news.title')) ?></h1>
  </div>
</section>
<section class="section">
  <div class="container grid grid-3">
    <?php if (!$items): ?>
      <p><?= e(t('news.empty')) ?></p>
    <?php endif; ?>
    <?php foreach ($items as $item): ?>
      <a class="card card-link list-card" href="<?= e(url('news-single.php?slug=' . urlencode($item['slug']))) ?>">
        <div class="cover"<?php if ($item['cover_image']): ?> style="background-image:url('<?= e(public_image($item['cover_image'])) ?>');background-size:cover"<?php endif; ?>></div>
        <p class="card-meta"><?= e($item['category']) ?> · <?= e(bn_date($item['published_at'])) ?></p>
        <h3><?= e($item['title']) ?></h3>
        <p><?= e(excerpt($item['body'], 140)) ?></p>
      </a>
    <?php endforeach; ?>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
