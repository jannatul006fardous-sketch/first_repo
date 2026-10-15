<?php
declare(strict_types=1);
require dirname(__DIR__) . '/includes/init.php';
require_admin();

$editId = (int) request('id', 0);
$item = $editId ? db_query('SELECT * FROM leadership WHERE id=?', [$editId])->fetch() : null;

if (is_post()) {
    require_csrf();
    if (request('action') === 'delete') {
        db_query('DELETE FROM leadership WHERE id=?', [(int) request('delete_id')]);
        flash('success', t('admin.deleted'));
        redirect('admin/leadership.php');
    }
    $photo = $item['photo'] ?? null;
    try {
        $photo = save_upload('photo', 'leadership', $photo);
    } catch (RuntimeException $ex) {
        flash('error', $ex->getMessage());
        redirect('admin/leadership.php?id=' . ($editId ?: 0));
    }
    $payload = [
        trim((string) request('name')),
        trim((string) request('role_bn')),
        (int) request('country_id') ?: null,
        $photo,
        trim((string) request('bio')) ?: null,
        (int) request('sort_order', 0),
        request('is_active') ? 1 : 0,
    ];
    if ($item) {
        db_query('UPDATE leadership SET name=?, role_bn=?, country_id=?, photo=?, bio=?, sort_order=?, is_active=? WHERE id=?', [...$payload, $item['id']]);
    } else {
        db_query('INSERT INTO leadership (name, role_bn, country_id, photo, bio, sort_order, is_active) VALUES (?,?,?,?,?,?,?)', $payload);
    }
    flash('success', t('admin.saved'));
    redirect('admin/leadership.php');
}

$list = db_query(
    'SELECT l.*, c.name_bn FROM leadership l LEFT JOIN countries c ON c.id=l.country_id ORDER BY l.sort_order, l.id'
)->fetchAll();
$countryList = countries(false);
$adminPage = t('admin.leadership');
require dirname(__DIR__) . '/includes/admin_header.php';
$row = $item ?: ['name'=>'','role_bn'=>'','country_id'=>'','bio'=>'','sort_order'=>0,'is_active'=>1];
?>
<h1><?= e(t('admin.leadership')) ?></h1>
<p><a class="btn btn-primary btn-sm" href="<?= e(url('admin/leadership.php?id=0')) ?>"><?= e(t('btn.add')) ?></a></p>
<?php if (isset($_GET['id'])): ?>
<form class="form card" method="post" enctype="multipart/form-data" style="margin-bottom:1.5rem">
  <?= csrf_field() ?>
  <div><label><?= e(t('label.name')) ?></label><input name="name" required value="<?= e($row['name']) ?>"></div>
  <div><label>পদবী (বাংলা)</label><input name="role_bn" required value="<?= e($row['role_bn']) ?>"></div>
  <div>
    <label><?= e(t('label.country')) ?></label>
    <select name="country_id">
      <option value="">—</option>
      <?php foreach ($countryList as $c): ?>
        <option value="<?= (int)$c['id'] ?>" <?= (int)$row['country_id']===(int)$c['id']?'selected':'' ?>><?= e($c['name_bn']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div><label>ক্রম</label><input type="number" name="sort_order" value="<?= e((string)$row['sort_order']) ?>"></div>
  <div><label><?= e(t('label.photo')) ?></label><input type="file" name="photo" accept="image/jpeg,image/png,image/webp"></div>
  <div><label><?= e(t('label.bio')) ?></label><textarea name="bio"><?= e($row['bio']) ?></textarea></div>
  <label><input type="checkbox" name="is_active" value="1" <?= !empty($row['is_active'])?'checked':'' ?>> <?= e(t('admin.active')) ?></label>
  <button class="btn btn-primary"><?= e(t('btn.save')) ?></button>
</form>
<?php endif; ?>
<div class="table-wrap card">
  <table class="data">
    <thead><tr><th><?= e(t('label.name')) ?></th><th>পদবী</th><th><?= e(t('label.country')) ?></th><th></th></tr></thead>
    <tbody>
    <?php foreach ($list as $p): ?>
      <tr>
        <td><?= e($p['name']) ?></td>
        <td><?= e($p['role_bn']) ?></td>
        <td><?= e($p['name_bn'] ?? '') ?></td>
        <td>
          <a href="<?= e(url('admin/leadership.php?id='.(int)$p['id'])) ?>"><?= e(t('btn.edit')) ?></a>
          <form method="post" style="display:inline" data-confirm="<?= e(t('admin.confirm_delete')) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="delete_id" value="<?= (int)$p['id'] ?>">
            <button class="btn btn-danger btn-sm"><?= e(t('btn.delete')) ?></button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php require dirname(__DIR__) . '/includes/admin_footer.php'; ?>
