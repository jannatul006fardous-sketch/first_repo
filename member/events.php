<?php
declare(strict_types=1);
require dirname(__DIR__) . '/includes/init.php';
$member = require_member();
$rsvps = db_query(
    'SELECT e.* FROM event_rsvps r JOIN events e ON e.id = r.event_id WHERE r.member_id = ? ORDER BY e.event_date DESC',
    [$member['id']]
)->fetchAll();
$meta = page_meta(t('member.my_events'));
require dirname(__DIR__) . '/includes/header.php';
?>
<section class="page-hero">
  <div class="container">
    <p class="kicker"><a href="<?= e(url('member/dashboard.php')) ?>"><?= e(t('member.dashboard')) ?></a></p>
    <h1><?= e(t('member.my_events')) ?></h1>
  </div>
</section>
<section class="section">
  <div class="container grid grid-2">
    <?php if (!$rsvps): ?><p><?= e(t('member.no_rsvp')) ?></p><?php endif; ?>
    <?php foreach ($rsvps as $ev): ?>
      <a class="card card-link" href="<?= e(url('event.php?id=' . (int)$ev['id'])) ?>">
        <p class="card-meta"><?= e(bn_date($ev['event_date'], 'd M Y, H:i')) ?></p>
        <h2><?= e($ev['title']) ?></h2>
      </a>
    <?php endforeach; ?>
  </div>
</section>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
