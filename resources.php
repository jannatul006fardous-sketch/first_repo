<?php
declare(strict_types=1);
require __DIR__ . '/includes/init.php';

$countryId = (int) request('country', 0);
$sql = 'SELECT r.*, c.name_bn AS country_name, c.iso_code
        FROM resources r
        LEFT JOIN countries c ON c.id = r.country_id';
$params = [];
if ($countryId) {
    $sql .= ' WHERE r.country_id IS NULL OR r.country_id = ?';
    $params[] = $countryId;
}
$sql .= ' ORDER BY r.created_at DESC';
$items = db_query($sql, $params)->fetchAll();
$countryList = countries(true);

$meta = page_meta(t('resources.title'));
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
  <div class="container">
    <h1><?= e(t('resources.title')) ?></h1>
    <p><?= e(t('resources.intro')) ?></p>
  </div>
</section>
<section class="section">
  <div class="container">
    <form method="get" class="form" style="max-width:320px;margin-bottom:1.5rem">
      <label for="country"><?= e(t('label.country')) ?></label>
      <select id="country" name="country" onchange="this.form.submit()">
        <option value="0">সব</option>
        <?php foreach ($countryList as $c): ?>
          <option value="<?= (int)$c['id'] ?>" <?= $countryId === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name_bn']) ?></option>
        <?php endforeach; ?>
      </select>
    </form>
    <p class="card muted"><?= e(t('resources.disclaimer')) ?></p>
    <div class="grid" style="margin-top:1rem">
      <?php if (!$items): ?><p><?= e(t('resources.empty')) ?></p><?php endif; ?>
      <?php foreach ($items as $r): ?>
        <article class="card">
          <p class="card-meta"><?= e($r['category']) ?><?php if ($r['country_name']): ?> · <?= flag_emoji($r['iso_code']) ?> <?= e($r['country_name']) ?><?php endif; ?></p>
          <h2><?= e($r['title']) ?></h2>
          <?= nl2p($r['body']) ?>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
