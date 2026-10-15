<?php
declare(strict_types=1);
require dirname(__DIR__) . '/includes/init.php';
require_admin();

$stats = [
    'members' => (int) db_query('SELECT COUNT(*) FROM members')->fetchColumn(),
    'pending' => (int) db_query("SELECT COUNT(*) FROM members WHERE status='pending'")->fetchColumn(),
    'chapters' => (int) db_query('SELECT COUNT(*) FROM countries WHERE is_active=1')->fetchColumn(),
    'messages' => (int) db_query('SELECT COUNT(*) FROM contact_messages WHERE is_read=0')->fetchColumn(),
];

$recentMembers = db_query('SELECT m.*, c.name_bn FROM members m LEFT JOIN countries c ON c.id=m.country_id ORDER BY m.created_at DESC LIMIT 6')->fetchAll();
$recentMessages = db_query('SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 5')->fetchAll();

$adminPage = t('admin.dashboard');
require dirname(__DIR__) . '/includes/admin_header.php';
?>
<h1><?= e(t('admin.dashboard')) ?></h1>
<div class="stat-grid">
  <div class="stat"><span><?= e(t('admin.members')) ?></span><b><?= (int)$stats['members'] ?></b></div>
  <div class="stat"><span><?= e(t('admin.pending')) ?></span><b><?= (int)$stats['pending'] ?></b></div>
  <div class="stat"><span><?= e(t('admin.chapters')) ?></span><b><?= (int)$stats['chapters'] ?></b></div>
  <div class="stat"><span><?= e(t('admin.unread')) ?></span><b><?= (int)$stats['messages'] ?></b></div>
</div>

<div class="split" style="margin-top:1.5rem">
  <div class="card">
    <h2><?= e(t('admin.members')) ?></h2>
    <ul>
      <?php foreach ($recentMembers as $m): ?>
        <li><?= e($m['name']) ?> · <?= e($m['name_bn'] ?? '') ?> · <?= e($m['status']) ?></li>
      <?php endforeach; ?>
    </ul>
    <a href="<?= e(url('admin/members.php')) ?>"><?= e(t('home.view_all')) ?></a>
  </div>
  <div class="card">
    <h2><?= e(t('admin.messages')) ?></h2>
    <ul>
      <?php foreach ($recentMessages as $msg): ?>
        <li><?= e($msg['name']) ?> — <?= e($msg['subject']) ?></li>
      <?php endforeach; ?>
    </ul>
    <a href="<?= e(url('admin/messages.php')) ?>"><?= e(t('home.view_all')) ?></a>
  </div>
</div>
<?php require dirname(__DIR__) . '/includes/admin_footer.php'; ?>
