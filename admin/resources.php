<?php
declare(strict_types=1);
require dirname(__DIR__) . '/includes/init.php';
require_admin();

$editId = (int) request('id', 0);
$item = $editId ? db_query('SELECT * FROM resources WHERE id=?', [$editId])->fetch() : null;

if (is_post()) {
    require_csrf();
    if (request('action') === 'delete') {
        db_query('DELETE FROM resources WHERE id=?', [(int) request('delete_id')]);
        flash('success', t('admin.deleted'));
        redirect('admin/resources.php');
    }
    $payload = [
        trim((string) request('title')),
        trim((string) request('body')),
        (int) request('country_id') ?: null,
        trim((string) request('category')) ?: 'সাধারণ',
    ];
    if ($item) {
        db_query('UPDATE resources SET title=?, body=?, country_id=?, category=? WHERE id=?', [...$payload, $item['id']]);
    } else {
        db_query('INSERT INTO resources (title, body, country_id, category) VALUES (?,?,?,?)', $payload);
    }
    flash('success', t('admin.saved'));
    redirect('admin/resources.php');
}

$list = db_query('SELECT r.*, c.name_bn FROM resources r LEFT JOIN countries c ON c.id=r.country_id ORDER BY r.created_at DESC')->fetchAll();
$countryList = countries(false);
$adminPage = t('admin.resources');
require dirname(__DIR__) . '/includes/admin_header.php';
$row = $item ?: ['title'=>'','body'=>'','country_id'=>'','category'=>'সাধারণ'];
?>
<h1><?= e(t('admin.resources')) ?></h1>
<p><a class="btn btn-primary btn-sm" href="<?= e(url('admin/resources.php?id=0')) ?>"><?= e(t('btn.add')) ?></a></p>
<?php if (isset($_GET['id'])): ?>
<form class="form card" method="post" style="margin-bottom:1.5rem">
  <?= csrf_field() ?>
  <div><label>শিরোনাম</label><input name="title" required value="<?= e($row['title']) ?>"></div>
  <div><label><?= e(t('label.category')) ?></label><input name="category" value="<?= e($row['category']) ?>"></div>
  <div>
    <label><?= e(t('label.country')) ?></label>
    <select name="country_id">
      <option value="">সাধারণ / সব দেশ</option>
      <?php foreach ($countryList as $c): ?>
        <option value="<?= (int)$c['id'] ?>" <?= (int)$row['country_id']===(int)$c['id']?'selected':'' ?>><?= e($c['name_bn']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div><label>লেখা</label><textarea name="body" required><?= e($row['body']) ?></textarea></div>
  <button class="btn btn-primary"><?= e(t('btn.save')) ?></button>
</form>
<?php endif; ?>
<div class="table-wrap card">
  <table class="data">
    <thead><tr><th>শিরোনাম</th><th><?= e(t('label.category')) ?></th><th><?= e(t('label.country')) ?></th><th></th></tr></thead>
    <tbody>
    <?php foreach ($list as $r): ?>
      <tr>
        <td><?= e($r['title']) ?></td>
        <td><?= e($r['category']) ?></td>
        <td><?= e($r['name_bn'] ?? '—') ?></td>
        <td>
          <a href="<?= e(url('admin/resources.php?id='.(int)$r['id'])) ?>"><?= e(t('btn.edit')) ?></a>
          <form method="post" style="display:inline" data-confirm="<?= e(t('admin.confirm_delete')) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="delete_id" value="<?= (int)$r['id'] ?>">
            <button class="btn btn-danger btn-sm"><?= e(t('btn.delete')) ?></button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php require dirname(__DIR__) . '/includes/admin_footer.php'; ?>
