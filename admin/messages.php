<?php
declare(strict_types=1);
require dirname(__DIR__) . '/includes/init.php';
require_admin();

if (is_post()) {
    require_csrf();
    $id = (int) request('id');
    if (request('action') === 'read') {
        db_query('UPDATE contact_messages SET is_read=1 WHERE id=?', [$id]);
    } elseif (request('action') === 'delete') {
        db_query('DELETE FROM contact_messages WHERE id=?', [$id]);
        flash('success', t('admin.deleted'));
    }
    redirect('admin/messages.php');
}

$viewId = (int) request('view', 0);
$view = $viewId ? db_query(
    'SELECT m.*, c.name_bn FROM contact_messages m LEFT JOIN countries c ON c.id=m.country_id WHERE m.id=?',
    [$viewId]
)->fetch() : null;
if ($view && !$view['is_read']) {
    db_query('UPDATE contact_messages SET is_read=1 WHERE id=?', [$viewId]);
    $view['is_read'] = 1;
}

$list = db_query(
    'SELECT m.*, c.name_bn FROM contact_messages m LEFT JOIN countries c ON c.id=m.country_id ORDER BY m.created_at DESC'
)->fetchAll();

$adminPage = t('admin.messages');
require dirname(__DIR__) . '/includes/admin_header.php';
?>
<h1><?= e(t('admin.messages')) ?></h1>
<?php if ($view): ?>
  <article class="card" style="margin-bottom:1.5rem">
    <h2><?= e($view['subject']) ?></h2>
    <p class="muted"><?= e($view['name']) ?> · <?= e($view['email']) ?> · <?= e($view['name_bn'] ?? '') ?> · <?= e(bn_date($view['created_at'], 'd M Y, H:i')) ?></p>
    <?= nl2p($view['message']) ?>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= (int)$view['id'] ?>">
      <input type="hidden" name="action" value="delete">
      <button class="btn btn-danger btn-sm" data-confirm="<?= e(t('admin.confirm_delete')) ?>"><?= e(t('btn.delete')) ?></button>
    </form>
  </article>
<?php endif; ?>
<div class="table-wrap card">
  <table class="data">
    <thead><tr><th><?= e(t('label.name')) ?></th><th><?= e(t('label.subject')) ?></th><th><?= e(t('label.date')) ?></th><th></th></tr></thead>
    <tbody>
    <?php foreach ($list as $m): ?>
      <tr>
        <td><?= $m['is_read'] ? '' : '● ' ?><?= e($m['name']) ?></td>
        <td><?= e($m['subject']) ?></td>
        <td><?= e(bn_date($m['created_at'])) ?></td>
        <td><a href="<?= e(url('admin/messages.php?view='.(int)$m['id'])) ?>"><?= e(t('btn.view')) ?></a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php require dirname(__DIR__) . '/includes/admin_footer.php'; ?>
