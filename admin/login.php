<?php
declare(strict_types=1);
require dirname(__DIR__) . '/includes/init.php';

if (current_admin()) {
    redirect('admin/index.php');
}

$errors = [];
if (is_post()) {
    require_csrf();
    if (rate_limited('admin_login', max_attempts(), attempt_window())) {
        $errors['form'] = t('auth.locked');
    } else {
        $email = trim((string) request('email'));
        $password = (string) request('password');
        $row = db_query('SELECT * FROM admins WHERE email = ?', [$email])->fetch();
        if (!$row || !password_verify($password, $row['password_hash'])) {
            rate_hit('admin_login');
            $errors['form'] = t('auth.invalid');
        } else {
            login_admin($row);
            redirect('admin/index.php');
        }
    }
}

$meta = page_meta(t('auth.admin_login'));
require dirname(__DIR__) . '/includes/header.php';
?>
<section class="section">
  <div class="container">
    <div class="card auth-card">
      <h1><?= e(t('auth.admin_login')) ?></h1>
      <?php if (!empty($errors['form'])): ?><p class="field-error"><?= e($errors['form']) ?></p><?php endif; ?>
      <form class="form" method="post" data-validate>
        <?= csrf_field() ?>
        <div>
          <label for="email"><?= e(t('label.email')) ?></label>
          <input id="email" type="email" name="email" required>
        </div>
        <div>
          <label for="password"><?= e(t('label.password')) ?></label>
          <input id="password" type="password" name="password" required>
        </div>
        <button class="btn btn-primary" type="submit"><?= e(t('btn.login')) ?></button>
      </form>
    </div>
  </div>
</section>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
