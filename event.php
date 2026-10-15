<?php
declare(strict_types=1);
require __DIR__ . '/includes/init.php';

$id = (int) request('id', 0);
$ev = $id ? db_query('SELECT * FROM events WHERE id = ?', [$id])->fetch() : null;
if (!$ev) {
    http_response_code(404);
    $meta = page_meta(t('err.not_found'));
    require __DIR__ . '/includes/header.php';
    echo '<section class="section"><div class="container"><h1>' . e(t('err.not_found')) . '</h1></div></section>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$member = current_member();
$errors = [];

if (is_post()) {
    require_csrf();
    if (rate_limited('rsvp', 10, 900)) {
        $errors['form'] = t('err.rate_limit');
    } else {
        $name = $member['name'] ?? trim((string) request('name'));
        $email = $member['email'] ?? trim((string) request('email'));
        if ($name === '' || $email === '') {
            $errors['form'] = t('err.required');
        } elseif (!valid_email($email)) {
            $errors['email'] = t('err.email');
        } else {
            rate_hit('rsvp');
            db_query(
                'INSERT INTO event_rsvps (event_id, member_id, name, email) VALUES (?,?,?,?)',
                [$ev['id'], $member['id'] ?? null, $name, $email]
            );
            flash('success', t('events.rsvp_success'));
            redirect('event.php?id=' . (int) $ev['id']);
        }
    }
}

$meta = page_meta($ev['title'], excerpt($ev['description'] ?? '', 160));
require __DIR__ . '/includes/header.php';
?>
<article class="section">
  <div class="container split">
    <div class="prose">
      <p class="kicker"><a href="<?= e(url('events.php')) ?>"><?= e(t('events.title')) ?></a></p>
      <h1><?= e($ev['title']) ?></h1>
      <p class="muted"><?= e(bn_date($ev['event_date'], 'd M Y, H:i')) ?></p>
      <p><?= $ev['is_online'] ? e(t('events.online')) : e($ev['location']) ?></p>
      <?php if ($ev['is_online'] && $ev['meeting_link']): ?>
        <p><a href="<?= e($ev['meeting_link']) ?>" rel="noopener noreferrer" target="_blank"><?= e(t('events.link')) ?></a></p>
      <?php endif; ?>
      <?= nl2p($ev['description'] ?? '') ?>
    </div>
    <div class="card">
      <h2><?= e(t('btn.rsvp')) ?></h2>
      <?php if (!$member): ?><p class="hint"><?= e(t('events.rsvp_guest')) ?></p><?php endif; ?>
      <?php if (!empty($errors['form'])): ?><p class="field-error"><?= e($errors['form']) ?></p><?php endif; ?>
      <form class="form" method="post" data-validate>
        <?= csrf_field() ?>
        <?php if (!$member): ?>
          <div>
            <label for="name"><?= e(t('label.name')) ?></label>
            <input id="name" name="name" required>
          </div>
          <div>
            <label for="email"><?= e(t('label.email')) ?></label>
            <input id="email" type="email" name="email" required>
          </div>
        <?php endif; ?>
        <button class="btn btn-accent" type="submit"><?= e(t('btn.rsvp')) ?></button>
      </form>
    </div>
  </div>
</article>
<?php require __DIR__ . '/includes/footer.php'; ?>
