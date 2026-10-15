<?php
declare(strict_types=1);
require dirname(__DIR__) . '/includes/init.php';
logout_admin();
flash('success', t('auth.logout_ok'));
redirect('admin/login.php');
