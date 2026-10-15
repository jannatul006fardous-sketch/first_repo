<?php
declare(strict_types=1);
require __DIR__ . '/includes/init.php';

$countryList = countries(true);
$errors = [];

if (is_post()) {
    require_csrf();
    if (rate_limited('membership', 6, 1800)) {
        $errors['form'] = t('err.rate_limit');
    } else {
        [$data, $errors] = require_post_fields(['name', 'email', 'phone', 'country_id', 'profession', 'bio']);
        $data['whatsapp'] = trim((string) request('whatsapp'));
        $data['membership_type'] = request('membership_type') === 'honorary' ? 'honorary' : 'general';
        $password = (string) request('password');
        $password2 = (string) request('password_confirm');

        if ($data['email'] && !valid_email($data['email'])) {
            $errors['email'] = t('err.email');
        }
        $exists = db_query('SELECT id FROM members WHERE email = ?', [$data['email']])->fetch();
        if ($exists) {
            $errors['email'] = t('err.duplicate_email');
        }
        if ($password !== '' || $password2 !== '') {
            if (strlen($password) < 8) {
                $errors['password'] = t('err.password_len');
            }
            if ($password !== $password2) {
                $errors['password_confirm'] = t('err.password_match');
            }
        }

        $photo = null;
        if (!$errors) {
            try {
                $photo = save_upload('photo', 'members');
            } catch (RuntimeException $e) {
                $errors['photo'] = $e->getMessage();
            }
        }

        if (!$errors) {
            rate_hit('membership');
            $hash = $password !== '' ? password_hash($password, PASSWORD_DEFAULT) : null;
            db_query(
                'INSERT INTO members (name, email, phone, whatsapp, country_id, profession, bio, photo, membership_type, status, password_hash)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?)',
                [
                    $data['name'], $data['email'], $data['phone'], $data['whatsapp'] ?: null,
                    (int) $data['country_id'], $data['profession'], $data['bio'], $photo,
                    $data['membership_type'], 'pending', $hash,
                ]
            );
            notify_admin(
                'নতুন সদস্য আবেদন',
                '<p>' . e($data['name']) . ' — ' . e($data['email']) . '</p><p>' . e($data['profession']) . '</p>'
            );
            flash('success', t('membership.success'));
            redirect('membership.php');
        }
        flash_old($_POST);
    }
}

$meta = page_meta(t('membership.title'), t('meta.membership'));
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
  <div class="container">
    <h1><?= e(t('membership.title')) ?></h1>
    <p><?= e(t('membership.not_fee')) ?></p>
  </div>
</section>
<section class="section">
  <div class="container split">
    <div>
      <h2><?= e(t('membership.why')) ?></h2>
      <ul>
        <li><?= e(t('membership.benefit_1')) ?></li>
        <li><?= e(t('membership.benefit_2')) ?></li>
        <li><?= e(t('membership.benefit_3')) ?></li>
        <li><?= e(t('membership.benefit_4')) ?></li>
      </ul>
      <h2><?= e(t('membership.types')) ?></h2>
      <div class="grid">
        <div class="card">
          <h3><?= e(t('membership.general')) ?></h3>
          <p>সকল প্রবাসী রেমিট্যান্স কর্মী ও সহযোগীদের জন্য উন্মুক্ত। ফি নেই।</p>
        </div>
        <div class="card">
          <h3><?= e(t('membership.honorary')) ?></h3>
          <p>বিশেষ অবদান বা দীর্ঘদিনের সহযোগিতার স্বীকৃতি। আবেদন করা যায়, অনুমোদন অ্যাডমিনের।</p>
        </div>
      </div>
    </div>
    <div class="card">
      <h2><?= e(t('membership.form_title')) ?></h2>
      <p class="hint"><?= e(t('membership.pending_note')) ?></p>
      <?php if (!empty($errors['form'])): ?><p class="field-error"><?= e($errors['form']) ?></p><?php endif; ?>
      <form class="form" method="post" enctype="multipart/form-data" data-validate>
        <?= csrf_field() ?>
        <div>
          <label for="name"><?= e(t('label.name')) ?></label>
          <input id="name" name="name" required value="<?= e(old('name')) ?>">
        </div>
        <div>
          <label for="email"><?= e(t('label.email')) ?></label>
          <input id="email" type="email" name="email" required value="<?= e(old('email')) ?>">
          <?php if (!empty($errors['email'])): ?><p class="field-error"><?= e($errors['email']) ?></p><?php endif; ?>
        </div>
        <div>
          <label for="phone"><?= e(t('label.phone')) ?></label>
          <input id="phone" name="phone" required value="<?= e(old('phone')) ?>">
        </div>
        <div>
          <label for="whatsapp"><?= e(t('label.whatsapp')) ?> <span class="hint">(<?= e(t('label.optional')) ?>)</span></label>
          <input id="whatsapp" name="whatsapp" value="<?= e(old('whatsapp')) ?>">
        </div>
        <div>
          <label for="country_id"><?= e(t('label.country')) ?></label>
          <select id="country_id" name="country_id" required>
            <option value="">—</option>
            <?php foreach ($countryList as $c): ?>
              <option value="<?= (int)$c['id'] ?>" <?= old('country_id') == $c['id'] ? 'selected' : '' ?>>
                <?= e($c['name_bn']) ?> (<?= e($c['name_en']) ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label for="profession"><?= e(t('label.profession')) ?></label>
          <input id="profession" name="profession" required value="<?= e(old('profession')) ?>">
        </div>
        <div>
          <label for="membership_type"><?= e(t('label.membership_type')) ?></label>
          <select id="membership_type" name="membership_type">
            <option value="general"><?= e(t('membership.general')) ?></option>
            <option value="honorary" <?= old('membership_type') === 'honorary' ? 'selected' : '' ?>><?= e(t('membership.honorary')) ?></option>
          </select>
        </div>
        <div>
          <label for="bio"><?= e(t('label.why_join')) ?></label>
          <textarea id="bio" name="bio" required><?= e(old('bio')) ?></textarea>
        </div>
        <div>
          <label for="photo"><?= e(t('label.photo')) ?> <span class="hint">(<?= e(t('label.optional')) ?>)</span></label>
          <input id="photo" type="file" name="photo" accept="image/jpeg,image/png,image/webp">
          <?php if (!empty($errors['photo'])): ?><p class="field-error"><?= e($errors['photo']) ?></p><?php endif; ?>
        </div>
        <div>
          <label for="password"><?= e(t('label.password')) ?> <span class="hint">(<?= e(t('label.optional')) ?> — অনুমোদনের পর লগইনের জন্য)</span></label>
          <input id="password" type="password" name="password" data-password minlength="8" autocomplete="new-password">
          <?php if (!empty($errors['password'])): ?><p class="field-error"><?= e($errors['password']) ?></p><?php endif; ?>
        </div>
        <div>
          <label for="password_confirm"><?= e(t('label.password_confirm')) ?></label>
          <input id="password_confirm" type="password" name="password_confirm" data-password-confirm autocomplete="new-password">
          <?php if (!empty($errors['password_confirm'])): ?><p class="field-error"><?= e($errors['password_confirm']) ?></p><?php endif; ?>
        </div>
        <button class="btn btn-accent" type="submit"><?= e(t('btn.apply')) ?></button>
      </form>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
