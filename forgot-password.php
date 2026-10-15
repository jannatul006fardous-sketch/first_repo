<?php
declare(strict_types=1);
require __DIR__ . '/includes/init.php';

if (is_post()) {
    require_csrf();
    if (!rate_limited('forgot', 5, 1800)) {
        rate_hit('forgot');
        $email = trim((string) request('email'));
        $member = valid_email($email)
            ? db_query("SELECT * FROM members WHERE email = ? AND status = 'approved'", [$email])->fetch()
            : null;
        if ($member) {
            $token = bin2hex(random_bytes(32));
            db_query('DELETE FROM password_resets WHERE email = ?', [$email]);
            db_query(
                'INSERT INTO password_resets (email, token_hash, expires_at) VALUES (?,?, DATE_ADD(NOW(), INTERVAL 2 HOUR))',
                [$email, hash('sha256', $token)]
            );
            $link = url('reset-password.php?token=' . $token);
            send_mail(
                $email,
                'REFA পাসওয়ার্ড রিসেট',
                mail_template('পাসওয়ার্ড রিসেট', '<p>লিংকটি ২ ঘণ্টা কাজ করবে:</p><p><a href="' . e($link) . '">' . e($link) . '</a></p>')
            );
        }
    }
    flash('success', t('auth.forgot_sent'));
    redirect('forgot-password.php');
}

$meta = page_meta(t('auth.forgot_title'));
require __DIR__ . '/includes/header.php';
?>
<section class="section">
  <div class="container">
    <div class="card auth-card">
      <h1><?= e(t('auth.forgot_title')) ?></h1>
      <form class="form" method="post" data-validate>
        <?= csrf_field() ?>
        <div>
          <label for="email"><?= e(t('label.email')) ?></label>
          <input id="email" type="email" name="email" required>
        </div>
        <button class="btn btn-primary" type="submit"><?= e(t('btn.reset')) ?></button>
      </form>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
