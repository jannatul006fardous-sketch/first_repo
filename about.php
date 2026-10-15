<?php
declare(strict_types=1);
require __DIR__ . '/includes/init.php';

$history = db_query("SELECT * FROM content_blocks WHERE block_key = 'about_history'")->fetch();
$mission = db_query("SELECT * FROM content_blocks WHERE block_key = 'about_mission'")->fetch();
$vision = db_query("SELECT * FROM content_blocks WHERE block_key = 'about_vision'")->fetch();
$leaders = db_query(
    'SELECT l.*, c.name_bn AS country_name, c.iso_code
     FROM leadership l
     LEFT JOIN countries c ON c.id = l.country_id
     WHERE l.is_active = 1
     ORDER BY l.sort_order, l.id'
)->fetchAll();

$meta = page_meta(t('about.title'), t('meta.about'));
require __DIR__ . '/includes/header.php';

function initials(string $name): string
{
    $parts = preg_split('/\s+/u', trim($name)) ?: [];
    $out = '';
    foreach (array_slice($parts, 0, 2) as $p) {
        $out .= mb_substr($p, 0, 1);
    }
    return $out !== '' ? $out : 'র';
}
?>
<section class="page-hero">
  <div class="container">
    <p class="kicker"><?= e(t('about.founded')) ?></p>
    <h1><?= e(t('about.title')) ?></h1>
    <p class="muted"><?= e(t('site.tagline')) ?> — কর্মীদের নেটওয়ার্ক, কর্পোরেট ব্যানার নয়।</p>
  </div>
</section>

<section class="section">
  <div class="container split">
    <div class="prose">
      <h2><?= e($history['title'] ?? t('about.history')) ?></h2>
      <?= nl2p($history['body'] ?? '') ?>
    </div>
    <div class="card">
      <h3><?= e($mission['title'] ?? t('about.mission')) ?></h3>
      <?= nl2p($mission['body'] ?? '') ?>
      <h3><?= e($vision['title'] ?? t('about.vision')) ?></h3>
      <?= nl2p($vision['body'] ?? '') ?>
    </div>
  </div>
</section>

<section class="section" style="padding-top:0">
  <div class="container">
    <h2><?= e(t('about.values')) ?></h2>
    <div class="grid grid-4 values">
      <?php foreach (['value.cooperation','value.solidarity','value.transparency','value.service'] as $key): ?>
        <div class="card">
          <h3><?= e(t($key)) ?></h3>
          <p><?= e(t($key . '_text')) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section" style="padding-top:0">
  <div class="container">
    <h2><?= e(t('about.leadership')) ?></h2>
    <div class="grid grid-3">
      <?php foreach ($leaders as $p): ?>
        <article class="card person-card">
          <div class="avatar">
            <?php if (!empty($p['photo']) && public_image($p['photo'])): ?>
              <img src="<?= e(public_image($p['photo'])) ?>" alt="<?= e($p['name']) ?>">
            <?php else: ?>
              <?= e(initials($p['name'])) ?>
            <?php endif; ?>
          </div>
          <h3><?= e($p['name']) ?></h3>
          <p class="card-meta"><?= e($p['role_bn']) ?></p>
          <?php if ($p['country_name']): ?>
            <p class="card-meta"><?= flag_emoji($p['iso_code']) ?> <?= e($p['country_name']) ?></p>
          <?php endif; ?>
          <?php if ($p['bio']): ?><p><?= e($p['bio']) ?></p><?php endif; ?>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
