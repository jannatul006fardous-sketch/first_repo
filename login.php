<?php
declare(strict_types=1);
require __DIR__ . '/includes/init.php';

if (current_member()) {
    redirect('member/dashboard.php');
}

$errors = [];
if (is_post()) {
    require_csrf();
    if (rate_limited('login', max_attempts(), attempt_window())) {
        $errors['form'] = t('auth.locked');
    } else {
        $email = trim((string) request('email'));
        $password = (string) request('password');
        $row = db_query('SELECT * FROM members WHERE email = ?', [$email])->fetch();
        $ok = $row && $row['password_hash'] && password_verify($password, $row['password_hash']);
        if (!$ok) {
            rate_hit('login');
            $errors['form'] = t('auth.invalid');
        } elseif ($row['status'] === 'pending') {
            $errors['form'] = t('auth.pending');
        } elseif ($row['status'] === 'rejected') {
            $errors['form'] = t('auth.rejected');
        } else {
            login_member($row);
            redirect('member/dashboard.php');
        }
    }
}

$meta = page_meta(t('auth.login_title'));
require __DIR__ . '/includes/header.php';
?>
<section class="section">
  <div class="container">
    <div class="card auth-card">
      <h1><?= e(t('auth.login_title')) ?></h1>
      <?php if (!empty($errors['form'])): ?><p class="field-error"><?= e($errors['form']) ?></p><?php endif; ?>
      <form class="form" method="post" data-validate>
        <?= csrf_field() ?>
        <div>
          <label for="email"><?= e(t('label.email')) ?></label>
          <input id="email" type="email" name="email" required value="<?= e(old('email')) ?>">
        </div>
        <div>
          <label for="password"><?= e(t('label.password')) ?></label>
          <input id="password" type="password" name="password" required>
        </div>
        <button class="btn btn-primary" type="submit"><?= e(t('btn.login')) ?></button>
      </form>
      <p><a href="<?= e(url('forgot-password.php')) ?>"><?= e(t('auth.forgot')) ?></a></p>
      <p><?= e(t('auth.no_account')) ?> <a href="<?= e(url('membership.php')) ?>"><?= e(t('nav.register')) ?></a></p>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
