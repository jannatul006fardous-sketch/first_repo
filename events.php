<?php
declare(strict_types=1);
require __DIR__ . '/includes/init.php';

$upcoming = db_query('SELECT * FROM events WHERE event_date >= NOW() ORDER BY event_date ASC')->fetchAll();
$past = db_query('SELECT * FROM events WHERE event_date < NOW() ORDER BY event_date DESC LIMIT 8')->fetchAll();

$meta = page_meta(t('events.title'));
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
  <div class="container">
    <h1><?= e(t('events.title')) ?></h1>
  </div>
</section>
<section class="section">
  <div class="container">
    <h2><?= e(t('events.upcoming')) ?></h2>
    <div class="grid grid-2">
      <?php if (!$upcoming): ?><p><?= e(t('events.empty')) ?></p><?php endif; ?>
      <?php foreach ($upcoming as $ev): ?>
        <a class="card card-link" href="<?= e(url('event.php?id=' . (int)$ev['id'])) ?>">
          <p class="card-meta"><?= e(bn_date($ev['event_date'], 'd M Y, H:i')) ?></p>
          <h3><?= e($ev['title']) ?></h3>
          <p><?= $ev['is_online'] ? e(t('events.online')) : e($ev['location']) ?></p>
        </a>
      <?php endforeach; ?>
    </div>
    <?php if ($past): ?>
      <h2 style="margin-top:2rem"><?= e(t('events.past')) ?></h2>
      <div class="grid grid-2">
        <?php foreach ($past as $ev): ?>
          <a class="card card-link" href="<?= e(url('event.php?id=' . (int)$ev['id'])) ?>">
            <p class="card-meta"><?= e(bn_date($ev['event_date'], 'd M Y')) ?></p>
            <h3><?= e($ev['title']) ?></h3>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
