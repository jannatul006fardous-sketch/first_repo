<?php
declare(strict_types=1);
require __DIR__ . '/includes/init.php';
logout_member();
flash('success', t('auth.logout_ok'));
redirect('index.php');
