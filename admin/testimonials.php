<?php
declare(strict_types=1);
require dirname(__DIR__) . '/includes/init.php';
require_admin();

$editId = (int) request('id', 0);
$item = $editId ? db_query('SELECT * FROM testimonials WHERE id=?', [$editId])->fetch() : null;

if (is_post()) {
    require_csrf();
    if (request('action') === 'delete') {
        db_query('DELETE FROM testimonials WHERE id=?', [(int) request('delete_id')]);
        flash('success', t('admin.deleted'));
        redirect('admin/testimonials.php');
    }
    $photo = $item['photo'] ?? null;
    try {
        $photo = save_upload('photo', 'testimonials', $photo);
    } catch (RuntimeException $ex) {
        flash('error', $ex->getMessage());
        redirect('admin/testimonials.php?id=' . ($editId ?: 0));
    }
    $payload = [
        trim((string) request('member_name')),
        (int) request('country_id') ?: null,
        trim((string) request('story')),
        $photo,
        request('is_featured') ? 1 : 0,
    ];
    if ($item) {
        db_query('UPDATE testimonials SET member_name=?, country_id=?, story=?, photo=?, is_featured=? WHERE id=?', [...$payload, $item['id']]);
    } else {
        db_query('INSERT INTO testimonials (member_name, country_id, story, photo, is_featured) VALUES (?,?,?,?,?)', $payload);
    }
    flash('success', t('admin.saved'));
    redirect('admin/testimonials.php');
}

$list = db_query(
    'SELECT t.*, c.name_bn FROM testimonials t LEFT JOIN countries c ON c.id=t.country_id ORDER BY t.created_at DESC'
)->fetchAll();
$countryList = countries(false);
$adminPage = t('admin.testimonials');
require dirname(__DIR__) . '/includes/admin_header.php';
$row = $item ?: ['member_name'=>'','country_id'=>'','story'=>'','is_featured'=>0];
?>
<h1><?= e(t('admin.testimonials')) ?></h1>
<p><a class="btn btn-primary btn-sm" href="<?= e(url('admin/testimonials.php?id=0')) ?>"><?= e(t('btn.add')) ?></a></p>
<?php if (isset($_GET['id'])): ?>
<form class="form card" method="post" enctype="multipart/form-data" style="margin-bottom:1.5rem">
  <?= csrf_field() ?>
  <div><label><?= e(t('label.name')) ?></label><input name="member_name" required value="<?= e($row['member_name']) ?>"></div>
  <div>
    <label><?= e(t('label.country')) ?></label>
    <select name="country_id">
      <option value="">—</option>
      <?php foreach ($countryList as $c): ?>
        <option value="<?= (int)$c['id'] ?>" <?= (int)$row['country_id']===(int)$c['id']?'selected':'' ?>><?= e($c['name_bn']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div><label>গল্প</label><textarea name="story" required><?= e($row['story']) ?></textarea></div>
  <div><label><?= e(t('label.photo')) ?></label><input type="file" name="photo" accept="image/jpeg,image/png,image/webp"></div>
  <label><input type="checkbox" name="is_featured" value="1" <?= !empty($row['is_featured'])?'checked':'' ?>> ফিচার্ড</label>
  <button class="btn btn-primary"><?= e(t('btn.save')) ?></button>
</form>
<?php endif; ?>
<div class="table-wrap card">
  <table class="data">
    <thead><tr><th><?= e(t('label.name')) ?></th><th><?= e(t('label.country')) ?></th><th></th></tr></thead>
    <tbody>
    <?php foreach ($list as $s): ?>
      <tr>
        <td><?= e($s['member_name']) ?></td>
        <td><?= e($s['name_bn'] ?? '') ?></td>
        <td>
          <a href="<?= e(url('admin/testimonials.php?id='.(int)$s['id'])) ?>"><?= e(t('btn.edit')) ?></a>
          <form method="post" style="display:inline" data-confirm="<?= e(t('admin.confirm_delete')) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="delete_id" value="<?= (int)$s['id'] ?>">
            <button class="btn btn-danger btn-sm"><?= e(t('btn.delete')) ?></button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php require dirname(__DIR__) . '/includes/admin_footer.php'; ?>
