<?php
declare(strict_types=1);

$meta = $meta ?? page_meta(t('nav.home'));
$member = current_member();
$admin = current_admin();
?>
<!DOCTYPE html>
<html lang="bn">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($meta['title']) ?></title>
  <meta name="description" content="<?= e($meta['description']) ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="icon" href="<?= e(asset('images/logo.png')) ?>" type="image/png">
  <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
</head>
<body>
  <a class="skip-link" href="#main"><?= e(t('nav.skip')) ?></a>
  <header class="site-header">
    <div class="container header-inner">
      <a class="brand" href="<?= e(url('index.php')) ?>">
        <img class="brand-logo" src="<?= e(asset('images/logo.png')) ?>" alt="<?= e(t('site.name')) ?> — <?= e(t('site.name_bn')) ?>" width="152" height="66">
      </a>
      <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-nav" data-nav-toggle>
        <span class="sr-only"><?= e(t('nav.menu')) ?></span>
        <span></span><span></span><span></span>
      </button>
      <nav class="site-nav" id="site-nav" data-nav>
        <a class="<?= nav_active('index.php') ?>" href="<?= e(url('index.php')) ?>"><?= e(t('nav.home')) ?></a>
        <a class="<?= nav_active('about.php') ?>" href="<?= e(url('about.php')) ?>"><?= e(t('nav.about')) ?></a>
        <a class="<?= nav_active('chapters.php') || nav_active('chapter.php') ? 'is-active' : '' ?>" href="<?= e(url('chapters.php')) ?>"><?= e(t('nav.chapters')) ?></a>
        <a class="<?= nav_active('membership.php') ?>" href="<?= e(url('membership.php')) ?>"><?= e(t('nav.membership')) ?></a>
        <a class="<?= nav_active('news.php') || nav_active('news-single.php') ? 'is-active' : '' ?>" href="<?= e(url('news.php')) ?>"><?= e(t('nav.news')) ?></a>
        <a class="<?= nav_active('events.php') || nav_active('event.php') ? 'is-active' : '' ?>" href="<?= e(url('events.php')) ?>"><?= e(t('nav.events')) ?></a>
        <a class="<?= nav_active('stories.php') ?>" href="<?= e(url('stories.php')) ?>"><?= e(t('nav.stories')) ?></a>
        <a class="<?= nav_active('resources.php') ?>" href="<?= e(url('resources.php')) ?>"><?= e(t('nav.resources')) ?></a>
        <a class="<?= nav_active('get-involved.php') ?>" href="<?= e(url('get-involved.php')) ?>"><?= e(t('nav.volunteer')) ?></a>
        <a class="<?= nav_active('contact.php') ?>" href="<?= e(url('contact.php')) ?>"><?= e(t('nav.contact')) ?></a>
        <div class="nav-cta">
          <?php if ($member): ?>
            <a class="btn btn-ghost" href="<?= e(url('member/dashboard.php')) ?>"><?= e(t('nav.dashboard')) ?></a>
          <?php elseif ($admin): ?>
            <a class="btn btn-ghost" href="<?= e(url('admin/index.php')) ?>"><?= e(t('admin.panel')) ?></a>
          <?php else: ?>
            <a class="btn btn-ghost" href="<?= e(url('login.php')) ?>"><?= e(t('nav.login')) ?></a>
            <a class="btn btn-accent" href="<?= e(url('membership.php')) ?>"><?= e(t('nav.register')) ?></a>
          <?php endif; ?>
        </div>
      </nav>
    </div>
  </header>
  <?php $allFlashes = flashes(); ?>
  <?php if ($allFlashes): ?>
    <div class="flash-wrap container">
      <?php foreach ($allFlashes as $type => $msg): ?>
        <div class="flash flash-<?= e($type === 'error' ? 'error' : ($type === 'success' ? 'success' : 'info')) ?>" role="status"><?= e($msg) ?></div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
  <main id="main">
