<?php
declare(strict_types=1);

function admin_nav(string $file): string
{
    return basename($_SERVER['SCRIPT_NAME'] ?? '') === $file ? 'is-active' : '';
}

$adminPage = $adminPage ?? t('admin.dashboard');
?><!DOCTYPE html>
<html lang="bn">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($adminPage) ?> · REFA</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="icon" href="<?= e(asset('images/favicon.svg')) ?>" type="image/svg+xml">
  <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
</head>
<body class="admin-body">
<div class="admin-shell">
  <aside class="admin-side">
    <p><strong>REFA</strong><br><span style="opacity:.8"><?= e(t('admin.panel')) ?></span></p>
    <nav>
      <a class="<?= admin_nav('index.php') ?>" href="<?= e(url('admin/index.php')) ?>"><?= e(t('admin.dashboard')) ?></a>
      <a class="<?= admin_nav('chapters.php') ?>" href="<?= e(url('admin/chapters.php')) ?>"><?= e(t('admin.chapters')) ?></a>
      <a class="<?= admin_nav('members.php') ?>" href="<?= e(url('admin/members.php')) ?>"><?= e(t('admin.members')) ?></a>
      <a class="<?= admin_nav('news.php') ?>" href="<?= e(url('admin/news.php')) ?>"><?= e(t('admin.news')) ?></a>
      <a class="<?= admin_nav('events.php') ?>" href="<?= e(url('admin/events.php')) ?>"><?= e(t('admin.events')) ?></a>
      <a class="<?= admin_nav('leadership.php') ?>" href="<?= e(url('admin/leadership.php')) ?>"><?= e(t('admin.leadership')) ?></a>
      <a class="<?= admin_nav('testimonials.php') ?>" href="<?= e(url('admin/testimonials.php')) ?>"><?= e(t('admin.testimonials')) ?></a>
      <a class="<?= admin_nav('resources.php') ?>" href="<?= e(url('admin/resources.php')) ?>"><?= e(t('admin.resources')) ?></a>
      <a class="<?= admin_nav('faqs.php') ?>" href="<?= e(url('admin/faqs.php')) ?>"><?= e(t('admin.faqs')) ?></a>
      <a class="<?= admin_nav('messages.php') ?>" href="<?= e(url('admin/messages.php')) ?>"><?= e(t('admin.messages')) ?></a>
      <a class="<?= admin_nav('volunteers.php') ?>" href="<?= e(url('admin/volunteers.php')) ?>"><?= e(t('admin.volunteers')) ?></a>
      <a class="<?= admin_nav('settings.php') ?>" href="<?= e(url('admin/settings.php')) ?>"><?= e(t('admin.settings')) ?></a>
      <a href="<?= e(url('index.php')) ?>">সাইট দেখুন</a>
      <a href="<?= e(url('admin/logout.php')) ?>"><?= e(t('nav.logout')) ?></a>
    </nav>
  </aside>
  <div class="admin-main">
    <?php foreach (flashes() as $type => $msg): ?>
      <div class="flash flash-<?= e($type === 'error' ? 'error' : ($type === 'success' ? 'success' : 'info')) ?>"><?= e($msg) ?></div>
    <?php endforeach; ?>
