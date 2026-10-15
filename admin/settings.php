<?php
declare(strict_types=1);
require dirname(__DIR__) . '/includes/init.php';
require_admin();

$settingKeys = [
    'hq_name' => 'সদর দপ্তরের নাম',
    'hq_address' => 'ঠিকানা',
    'hq_email' => 'ইমেইল',
    'hq_phone' => 'ফোন',
    'admin_email' => 'অ্যাডমিন নোটিফিকেশন ইমেইল',
    'map_embed' => 'মানচিত্র embed URL',
    'facebook_url' => 'Facebook URL',
    'whatsapp_url' => 'WhatsApp URL',
    'donate_enabled' => 'অনুদান পাতা চালু (1/0)',
];

$blockKeys = [
    'home_who' => 'হোম — আমরা কারা',
    'about_history' => 'ইতিহাস',
    'about_mission' => 'লক্ষ্য',
    'about_vision' => 'দৃষ্টিভঙ্গি',
    'donate_info' => 'অনুদান তথ্য',
];

if (is_post()) {
    require_csrf();
    foreach ($settingKeys as $key => $label) {
        db_query(
            'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)',
            [$key, trim((string) request('s_' . $key))]
        );
    }
    foreach ($blockKeys as $key => $label) {
        db_query(
            'INSERT INTO content_blocks (block_key, title, body) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE title=VALUES(title), body=VALUES(body)',
            [$key, trim((string) request('b_title_' . $key)), trim((string) request('b_body_' . $key))]
        );
    }
    flash('success', t('admin.saved'));
    redirect('admin/settings.php');
}

$blocks = [];
foreach (db_query('SELECT * FROM content_blocks')->fetchAll() as $b) {
    $blocks[$b['block_key']] = $b;
}

$adminPage = t('admin.settings');
require dirname(__DIR__) . '/includes/admin_header.php';
?>
<h1><?= e(t('admin.settings')) ?></h1>
<form class="form card" method="post">
  <?= csrf_field() ?>
  <h2>সাইট তথ্য</h2>
  <?php foreach ($settingKeys as $key => $label): ?>
    <div>
      <label for="s_<?= e($key) ?>"><?= e($label) ?></label>
      <?php if ($key === 'map_embed'): ?>
        <textarea id="s_<?= e($key) ?>" name="s_<?= e($key) ?>"><?= e(setting($key, '')) ?></textarea>
      <?php else: ?>
        <input id="s_<?= e($key) ?>" name="s_<?= e($key) ?>" value="<?= e(setting($key, '')) ?>">
      <?php endif; ?>
    </div>
  <?php endforeach; ?>

  <h2>পাতার লেখা</h2>
  <?php foreach ($blockKeys as $key => $label): ?>
    <fieldset class="card" style="margin-bottom:1rem">
      <legend><?= e($label) ?></legend>
      <div>
        <label>শিরোনাম</label>
        <input name="b_title_<?= e($key) ?>" value="<?= e($blocks[$key]['title'] ?? '') ?>">
      </div>
      <div>
        <label>লেখা</label>
        <textarea name="b_body_<?= e($key) ?>"><?= e($blocks[$key]['body'] ?? '') ?></textarea>
      </div>
    </fieldset>
  <?php endforeach; ?>
  <button class="btn btn-primary" type="submit"><?= e(t('btn.save')) ?></button>
</form>
<?php require dirname(__DIR__) . '/includes/admin_footer.php'; ?>
