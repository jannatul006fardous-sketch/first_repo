<?php
declare(strict_types=1);
require __DIR__ . '/includes/init.php';

$block = db_query("SELECT * FROM content_blocks WHERE block_key = 'donate_info'")->fetch();
$meta = page_meta(t('donate.title'));
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
  <div class="container">
    <h1><?= e(t('donate.title')) ?></h1>
    <p><?= e(t('donate.intro')) ?></p>
  </div>
</section>
<section class="section">
  <div class="container split">
    <div class="prose card">
      <h2><?= e($block['title'] ?? t('donate.title')) ?></h2>
      <?= nl2p($block['body'] ?? '') ?>
      <p class="hint"><?= e(t('donate.note')) ?></p>
    </div>
    <div class="cta-banner">
      <div>
        <h2><?= e(t('donate.contact_cta')) ?></h2>
        <p>বিষয় লিখুন “অনুদান” — আমরা ব্যাংক তথ্য বা পরবর্তী ধাপ জানাব।</p>
      </div>
      <a class="btn btn-accent" href="<?= e(url('contact.php')) ?>"><?= e(t('nav.contact')) ?></a>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
