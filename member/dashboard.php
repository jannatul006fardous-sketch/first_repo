<?php
declare(strict_types=1);
require dirname(__DIR__) . '/includes/init.php';
$member = require_member();

$chapter = $member['country_id']
    ? db_query('SELECT * FROM countries WHERE id = ?', [$member['country_id']])->fetch()
    : null;
$rsvps = db_query(
    'SELECT e.* FROM event_rsvps r JOIN events e ON e.id = r.event_id WHERE r.member_id = ? ORDER BY e.event_date DESC LIMIT 8',
    [$member['id']]
)->fetchAll();

$meta = page_meta(t('member.dashboard'));
require dirname(__DIR__) . '/includes/header.php';
?>
<section class="page-hero">
  <div class="container">
    <h1><?= e(t('member.welcome', ['name' => $member['name']])) ?></h1>
    <p>
      <a class="btn btn-ghost btn-sm" href="<?= e(url('member/profile.php')) ?>"><?= e(t('member.profile')) ?></a>
      <a class="btn btn-ghost btn-sm" href="<?= e(url('member/chapter.php')) ?>"><?= e(t('member.my_chapter')) ?></a>
      <a class="btn btn-ghost btn-sm" href="<?= e(url('member/events.php')) ?>"><?= e(t('member.my_events')) ?></a>
      <a class="btn btn-ghost btn-sm" href="<?= e(url('logout.php')) ?>"><?= e(t('nav.logout')) ?></a>
    </p>
  </div>
</section>
<section class="section">
  <div class="container split">
    <div class="card">
      <h2><?= e(t('member.my_chapter')) ?></h2>
      <?php if ($chapter): ?>
        <p><?= flag_emoji($chapter['iso_code']) ?> <strong><?= e($chapter['name_bn']) ?></strong></p>
        <p><?= e($chapter['coordinator_name']) ?> · <?= e($chapter['coordinator_contact']) ?></p>
        <p><a href="<?= e(url('chapter.php?slug=' . urlencode($chapter['slug']))) ?>"><?= e(t('btn.view')) ?></a></p>
      <?php else: ?>
        <p><?= e(t('member.no_chapter')) ?></p>
      <?php endif; ?>
    </div>
    <div class="card">
      <h2><?= e(t('member.my_events')) ?></h2>
      <?php if (!$rsvps): ?><p><?= e(t('member.no_rsvp')) ?></p><?php endif; ?>
      <ul>
        <?php foreach ($rsvps as $ev): ?>
          <li><a href="<?= e(url('event.php?id=' . (int)$ev['id'])) ?>"><?= e($ev['title']) ?></a> — <?= e(bn_date($ev['event_date'])) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</section>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
