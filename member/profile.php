<?php
declare(strict_types=1);
require dirname(__DIR__) . '/includes/init.php';
$member = require_member();
$countryList = countries(true);
$errors = [];

if (is_post()) {
    require_csrf();
    [$data, $errors] = require_post_fields(['name', 'phone', 'profession']);
    $data['whatsapp'] = trim((string) request('whatsapp'));
    $data['bio'] = trim((string) request('bio'));
    $data['country_id'] = (int) request('country_id') ?: null;
    $password = (string) request('password');
    $password2 = (string) request('password_confirm');
    if ($password !== '' || $password2 !== '') {
        if (strlen($password) < 8) {
            $errors['password'] = t('err.password_len');
        } elseif ($password !== $password2) {
            $errors['password_confirm'] = t('err.password_match');
        }
    }
    $photo = $member['photo'];
    if (!$errors) {
        try {
            $photo = save_upload('photo', 'members', $member['photo']);
        } catch (RuntimeException $ex) {
            $errors['photo'] = $ex->getMessage();
        }
    }
    if (!$errors) {
        db_query(
            'UPDATE members SET name=?, phone=?, whatsapp=?, country_id=?, profession=?, bio=?, photo=? WHERE id=?',
            [$data['name'], $data['phone'], $data['whatsapp'] ?: null, $data['country_id'], $data['profession'], $data['bio'] ?: null, $photo, $member['id']]
        );
        if ($password !== '') {
            db_query('UPDATE members SET password_hash=? WHERE id=?', [password_hash($password, PASSWORD_DEFAULT), $member['id']]);
        }
        flash('success', t('member.profile_updated'));
        redirect('member/profile.php');
    }
}

$meta = page_meta(t('member.profile'));
require dirname(__DIR__) . '/includes/header.php';
?>
<section class="page-hero">
  <div class="container">
    <p class="kicker"><a href="<?= e(url('member/dashboard.php')) ?>"><?= e(t('member.dashboard')) ?></a></p>
    <h1><?= e(t('member.profile')) ?></h1>
  </div>
</section>
<section class="section">
  <div class="container" style="max-width:640px">
    <form class="form card" method="post" enctype="multipart/form-data" data-validate>
      <?= csrf_field() ?>
      <div>
        <label for="name"><?= e(t('label.name')) ?></label>
        <input id="name" name="name" required value="<?= e($member['name']) ?>">
      </div>
      <div>
        <label><?= e(t('label.email')) ?></label>
        <input value="<?= e($member['email']) ?>" disabled>
      </div>
      <div>
        <label for="phone"><?= e(t('label.phone')) ?></label>
        <input id="phone" name="phone" required value="<?= e($member['phone']) ?>">
      </div>
      <div>
        <label for="whatsapp"><?= e(t('label.whatsapp')) ?></label>
        <input id="whatsapp" name="whatsapp" value="<?= e($member['whatsapp']) ?>">
      </div>
      <div>
        <label for="country_id"><?= e(t('label.country')) ?></label>
        <select id="country_id" name="country_id">
          <?php foreach ($countryList as $c): ?>
            <option value="<?= (int)$c['id'] ?>" <?= (int)$member['country_id'] === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name_bn']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label for="profession"><?= e(t('label.profession')) ?></label>
        <input id="profession" name="profession" required value="<?= e($member['profession']) ?>">
      </div>
      <div>
        <label for="bio"><?= e(t('label.bio')) ?></label>
        <textarea id="bio" name="bio"><?= e($member['bio']) ?></textarea>
      </div>
      <div>
        <label for="photo"><?= e(t('label.photo')) ?></label>
        <input id="photo" type="file" name="photo" accept="image/jpeg,image/png,image/webp">
      </div>
      <div>
        <label for="password"><?= e(t('label.new_password')) ?></label>
        <p class="hint"><?= e(t('member.password_optional')) ?></p>
        <input id="password" type="password" name="password" data-password minlength="8">
      </div>
      <div>
        <label for="password_confirm"><?= e(t('label.password_confirm')) ?></label>
        <input id="password_confirm" type="password" name="password_confirm" data-password-confirm>
        <?php if (!empty($errors['password'])): ?><p class="field-error"><?= e($errors['password']) ?></p><?php endif; ?>
        <?php if (!empty($errors['password_confirm'])): ?><p class="field-error"><?= e($errors['password_confirm']) ?></p><?php endif; ?>
      </div>
      <button class="btn btn-primary" type="submit"><?= e(t('btn.save')) ?></button>
    </form>
  </div>
</section>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
