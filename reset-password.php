<?php
declare(strict_types=1);
require __DIR__ . '/includes/init.php';

$token = (string) request('token', '');
$row = $token !== ''
    ? db_query(
        'SELECT * FROM password_resets WHERE token_hash = ? AND expires_at > NOW() ORDER BY id DESC LIMIT 1',
        [hash('sha256', $token)]
    )->fetch()
    : null;

if (!$row) {
    flash('error', t('err.generic'));
    redirect('forgot-password.php');
}

$errors = [];
if (is_post()) {
    require_csrf();
    $password = (string) request('password');
    $confirm = (string) request('password_confirm');
    if (strlen($password) < 8) {
        $errors['password'] = t('err.password_len');
    } elseif ($password !== $confirm) {
        $errors['password_confirm'] = t('err.password_match');
    } else {
        db_query(
            'UPDATE members SET password_hash = ? WHERE email = ? AND status = ?',
            [password_hash($password, PASSWORD_DEFAULT), $row['email'], 'approved']
        );
        db_query('DELETE FROM password_resets WHERE email = ?', [$row['email']]);
        flash('success', t('auth.reset_ok'));
        redirect('login.php');
    }
}

$meta = page_meta(t('auth.forgot_title'));
require __DIR__ . '/includes/header.php';
?>
<section class="section">
  <div class="container">
    <div class="card auth-card">
      <h1><?= e(t('label.new_password')) ?></h1>
      <form class="form" method="post" data-validate>
        <?= csrf_field() ?>
        <input type="hidden" name="token" value="<?= e($token) ?>">
        <div>
          <label for="password"><?= e(t('label.new_password')) ?></label>
          <input id="password" type="password" name="password" required minlength="8" data-password>
          <?php if (!empty($errors['password'])): ?><p class="field-error"><?= e($errors['password']) ?></p><?php endif; ?>
        </div>
        <div>
          <label for="password_confirm"><?= e(t('label.password_confirm')) ?></label>
          <input id="password_confirm" type="password" name="password_confirm" required data-password-confirm>
          <?php if (!empty($errors['password_confirm'])): ?><p class="field-error"><?= e($errors['password_confirm']) ?></p><?php endif; ?>
        </div>
        <button class="btn btn-primary" type="submit"><?= e(t('btn.save')) ?></button>
      </form>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
