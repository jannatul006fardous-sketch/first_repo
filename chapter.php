<?php
declare(strict_types=1);
require __DIR__ . '/includes/init.php';

$slug = trim((string) request('slug', ''));
$chapter = $slug !== '' ? country_by_slug($slug) : null;
if (!$chapter) {
    http_response_code(404);
    $meta = page_meta(t('err.not_found'));
    require __DIR__ . '/includes/header.php';
    echo '<section class="section"><div class="container"><h1>' . e(t('err.not_found')) . '</h1></div></section>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$errors = [];
if (is_post()) {
    require_csrf();
    if (rate_limited('contact', 8, 900)) {
        $errors['form'] = t('err.rate_limit');
    } else {
        [$data, $errors] = require_post_fields(['name', 'email', 'message']);
        if ($data['email'] && !valid_email($data['email'])) {
            $errors['email'] = t('err.email');
        }
        if (!$errors) {
            rate_hit('contact');
            db_query(
                'INSERT INTO contact_messages (name, email, subject, message, country_id) VALUES (?,?,?,?,?)',
                [$data['name'], $data['email'], 'চ্যাপ্টার: ' . $chapter['name_bn'], $data['message'], $chapter['id']]
            );
            notify_admin(
                'নতুন চ্যাপ্টার বার্তা — ' . $chapter['name_bn'],
                '<p><b>' . e($data['name']) . '</b> (' . e($data['email']) . ')</p><p>' . nl2br(e($data['message'])) . '</p>'
            );
            flash('success', t('contact.success'));
            redirect('chapter.php?slug=' . urlencode($chapter['slug']));
        }
        flash_old($_POST);
    }
}

$count = member_count_by_country((int) $chapter['id']);
$meta = page_meta($chapter['name_bn'] . ' — ' . t('chapters.detail'), excerpt($chapter['description'] ?? '', 150));
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
  <div class="container">
    <p class="kicker"><a href="<?= e(url('chapters.php')) ?>"><?= e(t('chapters.title')) ?></a></p>
    <h1><?= flag_emoji($chapter['iso_code']) ?> <?= e($chapter['name_bn']) ?></h1>
    <p class="muted"><?= e($chapter['name_en']) ?><?php if ($chapter['is_hq']): ?> · <?= e(t('home.hq_badge')) ?><?php endif; ?></p>
  </div>
</section>
<section class="section">
  <div class="container split">
    <div class="prose">
      <?= nl2p($chapter['description'] ?? '') ?>
      <?php if ($chapter['activities']): ?>
        <h2><?= e(t('chapters.activities')) ?></h2>
        <?= nl2p($chapter['activities']) ?>
      <?php endif; ?>
      <p class="card-meta"><?= e(t('home.members_count', ['count' => (string) $count])) ?></p>
    </div>
    <aside>
      <div class="card">
        <h3><?= e(t('label.coordinator')) ?></h3>
        <p><strong><?= e($chapter['coordinator_name'] ?: '—') ?></strong></p>
        <?php if ($chapter['coordinator_contact']): ?><p><?= e($chapter['coordinator_contact']) ?></p><?php endif; ?>
        <?php if ($chapter['coordinator_email']): ?>
          <p><a href="mailto:<?= e($chapter['coordinator_email']) ?>"><?= e($chapter['coordinator_email']) ?></a></p>
        <?php endif; ?>
      </div>
      <div class="card" style="margin-top:1rem">
        <h3><?= e(t('chapters.contact_form')) ?></h3>
        <?php if (!empty($errors['form'])): ?><p class="field-error"><?= e($errors['form']) ?></p><?php endif; ?>
        <form class="form" method="post" data-validate>
          <?= csrf_field() ?>
          <div>
            <label for="name"><?= e(t('label.name')) ?></label>
            <input id="name" name="name" required value="<?= e(old('name')) ?>">
          </div>
          <div>
            <label for="email"><?= e(t('label.email')) ?></label>
            <input id="email" type="email" name="email" required value="<?= e(old('email')) ?>">
          </div>
          <div>
            <label for="message"><?= e(t('label.message')) ?></label>
            <textarea id="message" name="message" required><?= e(old('message')) ?></textarea>
          </div>
          <button class="btn btn-primary" type="submit"><?= e(t('btn.send')) ?></button>
        </form>
      </div>
    </aside>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
