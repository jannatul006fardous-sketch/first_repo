<?php
declare(strict_types=1);
require __DIR__ . '/includes/init.php';

$countryList = countries(true);
$errors = [];

if (is_post()) {
    require_csrf();
    if (rate_limited('contact', 8, 900)) {
        $errors['form'] = t('err.rate_limit');
    } else {
        [$data, $errors] = require_post_fields(['name', 'email', 'subject', 'message']);
        $countryId = (int) request('country_id') ?: null;
        if ($data['email'] && !valid_email($data['email'])) {
            $errors['email'] = t('err.email');
        }
        if (!$errors) {
            rate_hit('contact');
            db_query(
                'INSERT INTO contact_messages (name, email, subject, message, country_id) VALUES (?,?,?,?,?)',
                [$data['name'], $data['email'], $data['subject'], $data['message'], $countryId]
            );
            notify_admin('নতুন যোগাযোগ বার্তা: ' . $data['subject'], '<p>' . e($data['name']) . ' (' . e($data['email']) . ')</p>' . nl2p($data['message']));
            send_mail($data['email'], 'REFA — বার্তা পেয়েছি', mail_template('ধন্যবাদ', '<p>আপনার বার্তা পেয়েছি। শিগগির উত্তর দেব।</p>'));
            flash('success', t('contact.success'));
            redirect('contact.php');
        }
        flash_old($_POST);
    }
}

$map = setting('map_embed', 'https://maps.google.com/maps?q=Helsinki%2C%20Finland&t=&z=6&ie=UTF8&iwloc=&output=embed');
$meta = page_meta(t('contact.title'), t('meta.contact'));
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
  <div class="container">
    <h1><?= e(t('contact.title')) ?></h1>
    <p><?= e(t('contact.intro')) ?></p>
  </div>
</section>
<section class="section">
  <div class="container split">
    <div>
      <div class="card">
        <h2><?= e(t('contact.hq')) ?></h2>
        <p><?= e(setting('hq_name')) ?></p>
        <p><?= e(setting('hq_address')) ?></p>
        <p><a href="mailto:<?= e(setting('hq_email')) ?>"><?= e(setting('hq_email')) ?></a></p>
        <p><?= e(setting('hq_phone')) ?></p>
      </div>
      <div class="map-wrap" style="margin-top:1rem">
        <iframe title="<?= e(t('contact.map')) ?>" src="<?= e($map) ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
      </div>
      <h2 style="margin-top:1.5rem"><?= e(t('contact.regionals')) ?></h2>
      <div class="grid">
        <?php foreach ($countryList as $c): ?>
          <div class="card">
            <h3><?= flag_emoji($c['iso_code']) ?> <?= e($c['name_bn']) ?></h3>
            <p><?= e($c['coordinator_name'] ?: '—') ?></p>
            <p class="card-meta"><?= e($c['coordinator_contact'] ?: '') ?></p>
            <?php if ($c['coordinator_email']): ?>
              <p><a href="mailto:<?= e($c['coordinator_email']) ?>"><?= e($c['coordinator_email']) ?></a></p>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="card">
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
          <label for="country_id"><?= e(t('label.country')) ?> <span class="hint">(<?= e(t('label.optional')) ?>)</span></label>
          <select id="country_id" name="country_id">
            <option value="">—</option>
            <?php foreach ($countryList as $c): ?>
              <option value="<?= (int)$c['id'] ?>" <?= old('country_id') == $c['id'] ? 'selected' : '' ?>><?= e($c['name_bn']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label for="subject"><?= e(t('label.subject')) ?></label>
          <input id="subject" name="subject" required value="<?= e(old('subject')) ?>">
        </div>
        <div>
          <label for="message"><?= e(t('label.message')) ?></label>
          <textarea id="message" name="message" required><?= e(old('message')) ?></textarea>
        </div>
        <button class="btn btn-primary" type="submit"><?= e(t('btn.send')) ?></button>
      </form>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
