<?php
declare(strict_types=1);
require dirname(__DIR__) . '/includes/init.php';
require_admin();

$editId = (int) request('id', 0);
$item = $editId ? db_query('SELECT * FROM faqs WHERE id=?', [$editId])->fetch() : null;

if (is_post()) {
    require_csrf();
    if (request('action') === 'delete') {
        db_query('DELETE FROM faqs WHERE id=?', [(int) request('delete_id')]);
        flash('success', t('admin.deleted'));
        redirect('admin/faqs.php');
    }
    $payload = [
        trim((string) request('question')),
        trim((string) request('answer')),
        (int) request('sort_order', 0),
        request('is_active') ? 1 : 0,
    ];
    if ($item) {
        db_query('UPDATE faqs SET question=?, answer=?, sort_order=?, is_active=? WHERE id=?', [...$payload, $item['id']]);
    } else {
        db_query('INSERT INTO faqs (question, answer, sort_order, is_active) VALUES (?,?,?,?)', $payload);
    }
    flash('success', t('admin.saved'));
    redirect('admin/faqs.php');
}

$list = db_query('SELECT * FROM faqs ORDER BY sort_order, id')->fetchAll();
$adminPage = t('admin.faqs');
require dirname(__DIR__) . '/includes/admin_header.php';
$row = $item ?: ['question'=>'','answer'=>'','sort_order'=>0,'is_active'=>1];
?>
<h1><?= e(t('admin.faqs')) ?></h1>
<p><a class="btn btn-primary btn-sm" href="<?= e(url('admin/faqs.php?id=0')) ?>"><?= e(t('btn.add')) ?></a></p>
<?php if (isset($_GET['id'])): ?>
<form class="form card" method="post" style="margin-bottom:1.5rem">
  <?= csrf_field() ?>
  <div><label>প্রশ্ন</label><input name="question" required value="<?= e($row['question']) ?>"></div>
  <div><label>উত্তর</label><textarea name="answer" required><?= e($row['answer']) ?></textarea></div>
  <div><label>ক্রম</label><input type="number" name="sort_order" value="<?= e((string)$row['sort_order']) ?>"></div>
  <label><input type="checkbox" name="is_active" value="1" <?= !empty($row['is_active'])?'checked':'' ?>> <?= e(t('admin.active')) ?></label>
  <button class="btn btn-primary"><?= e(t('btn.save')) ?></button>
</form>
<?php endif; ?>
<div class="table-wrap card">
  <table class="data">
    <thead><tr><th>প্রশ্ন</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($list as $f): ?>
      <tr>
        <td><?= e($f['question']) ?></td>
        <td>
          <a href="<?= e(url('admin/faqs.php?id='.(int)$f['id'])) ?>"><?= e(t('btn.edit')) ?></a>
          <form method="post" style="display:inline" data-confirm="<?= e(t('admin.confirm_delete')) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="delete_id" value="<?= (int)$f['id'] ?>">
            <button class="btn btn-danger btn-sm"><?= e(t('btn.delete')) ?></button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php require dirname(__DIR__) . '/includes/admin_footer.php'; ?>
