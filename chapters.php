<?php
declare(strict_types=1);
require __DIR__ . '/includes/init.php';

$list = db_query(
    'SELECT c.*,
      (SELECT COUNT(*) FROM members m WHERE m.country_id = c.id AND m.status = "approved") AS member_count
     FROM countries c
     WHERE c.is_active = 1
     ORDER BY c.is_hq DESC, c.sort_order, c.name_bn'
)->fetchAll();

$meta = page_meta(t('chapters.title'), t('meta.chapters'));
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
  <div class="container">
    <h1><?= e(t('chapters.title')) ?></h1>
    <p><?= e(t('chapters.intro')) ?></p>
  </div>
</section>
<section class="section">
  <div class="container">
    <?php if (!$list): ?>
      <p><?= e(t('chapters.no_chapters')) ?></p>
    <?php else: ?>
      <div class="grid grid-3">
        <?php foreach ($list as $c): ?>
          <a class="card card-link" href="<?= e(url('chapter.php?slug=' . urlencode($c['slug']))) ?>">
            <p class="flag" style="font-size:2rem;margin:0"><?= flag_emoji($c['iso_code']) ?></p>
            <h3><?= e($c['name_bn']) ?> <span class="muted" style="font-weight:500;font-size:0.95rem"><?= e($c['name_en']) ?></span></h3>
            <?php if ($c['is_hq']): ?><span class="badge"><?= e(t('home.hq_badge')) ?></span><?php endif; ?>
            <p><?= e(excerpt($c['description'] ?? '', 140)) ?></p>
            <p class="card-meta"><?= e(t('home.members_count', ['count' => (string) $c['member_count']])) ?></p>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
