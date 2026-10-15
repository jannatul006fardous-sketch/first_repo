<?php
declare(strict_types=1);
require dirname(__DIR__) . '/includes/init.php';
require_admin();

$editId = (int) request('id', 0);
$item = $editId ? db_query('SELECT * FROM countries WHERE id=?', [$editId])->fetch() : null;

if (is_post()) {
    require_csrf();
    $action = (string) request('action', 'save');
    if ($action === 'delete') {
        db_query('DELETE FROM countries WHERE id=?', [(int) request('delete_id')]);
        flash('success', t('admin.deleted'));
        redirect('admin/chapters.php');
    }
    $slug = slugify((string) request('slug') ?: (string) request('name_en'));
    $slug = unique_slug('countries', $slug, $editId ?: null);
    $iso = strtoupper(substr(trim((string) request('iso_code')), 0, 2));
    $data = [
        $slug,
        $iso,
        trim((string) request('name_bn')),
        trim((string) request('name_en')),
        trim((string) request('coordinator_name')) ?: null,
        trim((string) request('coordinator_contact')) ?: null,
        trim((string) request('coordinator_email')) ?: null,
        trim((string) request('description')) ?: null,
        trim((string) request('activities')) ?: null,
        request('is_hq') ? 1 : 0,
        request('is_active') ? 1 : 0,
        (int) request('sort_order', 0),
    ];
    if ($item) {
        db_query(
            'UPDATE countries SET slug=?, iso_code=?, name_bn=?, name_en=?, coordinator_name=?, coordinator_contact=?, coordinator_email=?, description=?, activities=?, is_hq=?, is_active=?, sort_order=? WHERE id=?',
            [...$data, $item['id']]
        );
    } else {
        db_query(
            'INSERT INTO countries (slug, iso_code, name_bn, name_en, coordinator_name, coordinator_contact, coordinator_email, description, activities, is_hq, is_active, sort_order)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?)',
            $data
        );
    }
    flash('success', t('admin.saved'));
    redirect('admin/chapters.php');
}

$list = db_query('SELECT * FROM countries ORDER BY sort_order, name_bn')->fetchAll();
$adminPage = t('admin.chapters');
require dirname(__DIR__) . '/includes/admin_header.php';
?>
<h1><?= e(t('admin.chapters')) ?></h1>
<p><a class="btn btn-primary btn-sm" href="<?= e(url('admin/chapters.php?id=0')) ?>"><?= e(t('btn.add')) ?></a></p>

<?php if (isset($_GET['id'])): ?>
  <?php $row = $item ?: ['slug'=>'','iso_code'=>'','name_bn'=>'','name_en'=>'','coordinator_name'=>'','coordinator_contact'=>'','coordinator_email'=>'','description'=>'','activities'=>'','is_hq'=>0,'is_active'=>1,'sort_order'=>0]; ?>
  <form class="form card" method="post" style="margin-bottom:1.5rem">
    <?= csrf_field() ?>
    <div class="grid grid-2">
      <div>
        <label>নাম (বাংলা)</label>
        <input name="name_bn" required value="<?= e($row['name_bn']) ?>">
      </div>
      <div>
        <label>Name (EN)</label>
        <input name="name_en" required value="<?= e($row['name_en']) ?>">
      </div>
      <div>
        <label>slug</label>
        <input name="slug" value="<?= e($row['slug']) ?>">
      </div>
      <div>
        <label>ISO</label>
        <input name="iso_code" maxlength="2" required value="<?= e($row['iso_code']) ?>">
      </div>
      <div>
        <label><?= e(t('label.coordinator')) ?></label>
        <input name="coordinator_name" value="<?= e($row['coordinator_name']) ?>">
      </div>
      <div>
        <label><?= e(t('label.contact')) ?></label>
        <input name="coordinator_contact" value="<?= e($row['coordinator_contact']) ?>">
      </div>
      <div>
        <label>সমন্বয়ক ইমেইল</label>
        <input type="email" name="coordinator_email" value="<?= e($row['coordinator_email']) ?>">
      </div>
      <div>
        <label>ক্রম</label>
        <input type="number" name="sort_order" value="<?= e((string)$row['sort_order']) ?>">
      </div>
    </div>
    <div>
      <label>বিবরণ</label>
      <textarea name="description"><?= e($row['description']) ?></textarea>
    </div>
    <div>
      <label><?= e(t('chapters.activities')) ?></label>
      <textarea name="activities"><?= e($row['activities']) ?></textarea>
    </div>
    <label><input type="checkbox" name="is_hq" value="1" <?= !empty($row['is_hq']) ? 'checked' : '' ?>> সদর দপ্তর</label>
    <label><input type="checkbox" name="is_active" value="1" <?= !empty($row['is_active']) ? 'checked' : '' ?>> <?= e(t('admin.active')) ?></label>
    <button class="btn btn-primary" type="submit"><?= e(t('btn.save')) ?></button>
    <a class="btn btn-ghost" href="<?= e(url('admin/chapters.php')) ?>"><?= e(t('btn.cancel')) ?></a>
  </form>
<?php endif; ?>

<div class="table-wrap card">
  <table class="data">
    <thead>
      <tr>
        <th>দেশ</th>
        <th>ISO</th>
        <th><?= e(t('label.coordinator')) ?></th>
        <th><?= e(t('label.status')) ?></th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($list as $c): ?>
        <tr>
          <td><?= flag_emoji($c['iso_code']) ?> <?= e($c['name_bn']) ?></td>
          <td><?= e($c['iso_code']) ?></td>
          <td><?= e($c['coordinator_name']) ?></td>
          <td><?= $c['is_active'] ? e(t('admin.active')) : e(t('admin.inactive')) ?></td>
          <td>
            <a href="<?= e(url('admin/chapters.php?id=' . (int)$c['id'])) ?>"><?= e(t('btn.edit')) ?></a>
            <form method="post" style="display:inline" data-confirm="<?= e(t('admin.confirm_delete')) ?>">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="delete_id" value="<?= (int)$c['id'] ?>">
              <button class="btn btn-danger btn-sm" type="submit"><?= e(t('btn.delete')) ?></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php require dirname(__DIR__) . '/includes/admin_footer.php'; ?>
