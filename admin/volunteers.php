<?php
declare(strict_types=1);
require dirname(__DIR__) . '/includes/init.php';
require_admin();

if (is_post() && request('action') === 'delete') {
    require_csrf();
    db_query('DELETE FROM volunteer_interest WHERE id=?', [(int) request('id')]);
    flash('success', t('admin.deleted'));
    redirect('admin/volunteers.php');
}

$list = db_query(
    'SELECT v.*, c.name_bn FROM volunteer_interest v LEFT JOIN countries c ON c.id=v.country_id ORDER BY v.created_at DESC'
)->fetchAll();

$adminPage = t('admin.volunteers');
require dirname(__DIR__) . '/includes/admin_header.php';
?>
<h1><?= e(t('admin.volunteers')) ?></h1>
<div class="table-wrap card">
  <table class="data">
    <thead>
      <tr>
        <th><?= e(t('label.name')) ?></th>
        <th><?= e(t('label.email')) ?></th>
        <th><?= e(t('label.interest')) ?></th>
        <th><?= e(t('label.country')) ?></th>
        <th><?= e(t('label.message')) ?></th>
        <th></th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($list as $v): ?>
      <tr>
        <td><?= e($v['name']) ?></td>
        <td><?= e($v['email']) ?><br><?= e($v['phone']) ?></td>
        <td><?= e($v['interest_area']) ?></td>
        <td><?= e($v['name_bn'] ?? '') ?></td>
        <td><?= e(excerpt($v['message'] ?? '', 80)) ?></td>
        <td>
          <form method="post" data-confirm="<?= e(t('admin.confirm_delete')) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= (int)$v['id'] ?>">
            <button class="btn btn-danger btn-sm"><?= e(t('btn.delete')) ?></button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$list): ?>
      <tr><td colspan="6"><?= e(t('admin.no_items')) ?></td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>
<?php require dirname(__DIR__) . '/includes/admin_footer.php'; ?>
