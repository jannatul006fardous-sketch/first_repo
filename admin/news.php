<?php
declare(strict_types=1);
require dirname(__DIR__) . '/includes/init.php';
$adminUser = require_admin();

$editId = (int) request('id', 0);
$item = $editId ? db_query('SELECT * FROM news WHERE id=?', [$editId])->fetch() : null;

if (is_post()) {
    require_csrf();
    if (request('action') === 'delete') {
        db_query('DELETE FROM news WHERE id=?', [(int) request('delete_id')]);
        flash('success', t('admin.deleted'));
        redirect('admin/news.php');
    }
    $title = trim((string) request('title'));
    $slug = unique_slug('news', slugify((string) request('slug') ?: $title), $editId ?: null);
    $body = trim((string) request('body'));
    $category = trim((string) request('category')) ?: 'সাধারণ';
    $published = trim((string) request('published_at')) ?: date('Y-m-d H:i:s');
    $cover = $item['cover_image'] ?? null;
    try {
        $cover = save_upload('cover_image', 'news', $cover);
    } catch (RuntimeException $ex) {
        flash('error', $ex->getMessage());
        redirect('admin/news.php' . ($editId ? '?id=' . $editId : '?id=0'));
    }
    if ($item) {
        db_query(
            'UPDATE news SET title=?, slug=?, body=?, cover_image=?, category=?, published_at=? WHERE id=?',
            [$title, $slug, $body, $cover, $category, $published, $item['id']]
        );
    } else {
        db_query(
            'INSERT INTO news (title, slug, body, cover_image, category, published_at, created_by) VALUES (?,?,?,?,?,?,?)',
            [$title, $slug, $body, $cover, $category, $published, $adminUser['id']]
        );
    }
    flash('success', t('admin.saved'));
    redirect('admin/news.php');
}

$list = db_query('SELECT * FROM news ORDER BY published_at DESC, id DESC')->fetchAll();
$adminPage = t('admin.news');
require dirname(__DIR__) . '/includes/admin_header.php';
$row = $item ?: ['title'=>'','slug'=>'','body'=>'','category'=>'সাধারণ','published_at'=>date('Y-m-d\TH:i')];
?>
<h1><?= e(t('admin.news')) ?></h1>
<p><a class="btn btn-primary btn-sm" href="<?= e(url('admin/news.php?id=0')) ?>"><?= e(t('btn.add')) ?></a></p>

<?php if (isset($_GET['id'])): ?>
<form class="form card" method="post" enctype="multipart/form-data" style="margin-bottom:1.5rem">
  <?= csrf_field() ?>
  <div><label>শিরোনাম</label><input name="title" required value="<?= e($row['title']) ?>"></div>
  <div><label>slug</label><input name="slug" value="<?= e($row['slug'] ?? '') ?>"></div>
  <div><label><?= e(t('label.category')) ?></label><input name="category" value="<?= e($row['category']) ?>"></div>
  <div><label><?= e(t('label.date')) ?></label>
    <input type="datetime-local" name="published_at" value="<?= e(isset($row['published_at']) && $row['published_at'] ? date('Y-m-d\TH:i', strtotime($row['published_at'])) : date('Y-m-d\TH:i')) ?>">
  </div>
  <div><label>কভার ছবি</label><input type="file" name="cover_image" accept="image/jpeg,image/png,image/webp"></div>
  <div><label>লেখা</label><textarea name="body" required><?= e($row['body']) ?></textarea></div>
  <button class="btn btn-primary"><?= e(t('btn.save')) ?></button>
  <a class="btn btn-ghost" href="<?= e(url('admin/news.php')) ?>"><?= e(t('btn.cancel')) ?></a>
</form>
<?php endif; ?>

<div class="table-wrap card">
  <table class="data">
    <thead><tr><th>শিরোনাম</th><th><?= e(t('label.date')) ?></th><th></th></tr></thead>
    <tbody>
    <?php foreach ($list as $n): ?>
      <tr>
        <td><?= e($n['title']) ?></td>
        <td><?= e(bn_date($n['published_at'])) ?></td>
        <td>
          <a href="<?= e(url('admin/news.php?id='.(int)$n['id'])) ?>"><?= e(t('btn.edit')) ?></a>
          <form method="post" style="display:inline" data-confirm="<?= e(t('admin.confirm_delete')) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="delete_id" value="<?= (int)$n['id'] ?>">
            <button class="btn btn-danger btn-sm"><?= e(t('btn.delete')) ?></button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php require dirname(__DIR__) . '/includes/admin_footer.php'; ?>
