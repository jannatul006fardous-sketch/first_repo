<?php
declare(strict_types=1);
require __DIR__ . '/includes/init.php';

$stories = db_query(
    'SELECT t.*, c.name_bn AS country_name, c.iso_code
     FROM testimonials t
     LEFT JOIN countries c ON c.id = t.country_id
     ORDER BY t.is_featured DESC, t.created_at DESC'
)->fetchAll();

$meta = page_meta(t('stories.title'));
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
  <div class="container">
    <h1><?= e(t('stories.title')) ?></h1>
    <p><?= e(t('stories.intro')) ?></p>
  </div>
</section>
<section class="section">
  <div class="container grid grid-2">
    <?php if (!$stories): ?><p><?= e(t('stories.empty')) ?></p><?php endif; ?>
    <?php foreach ($stories as $s): ?>
      <article class="card story-card">
        <p><?= e($s['story']) ?></p>
        <p class="card-meta">
          <?= e($s['member_name']) ?>
          <?php if ($s['country_name']): ?> · <?= flag_emoji($s['iso_code']) ?> <?= e($s['country_name']) ?><?php endif; ?>
        </p>
      </article>
    <?php endforeach; ?>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
