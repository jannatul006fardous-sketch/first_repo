<?php
declare(strict_types=1);
require __DIR__ . '/includes/init.php';

if (is_post()) {
    require_csrf();
    $email = trim((string) request('email'));
    if (valid_email($email) && !rate_limited('newsletter', 8, 1800)) {
        rate_hit('newsletter');
        try {
            db_query('INSERT INTO newsletter_subscribers (email) VALUES (?)', [$email]);
            flash('success', t('ok.subscribed'));
        } catch (PDOException $e) {
            flash('info', t('ok.already_subscribed'));
        }
    } else {
        flash('error', t('err.email'));
    }
}
redirect('index.php');
