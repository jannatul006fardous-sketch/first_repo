<?php
declare(strict_types=1);
require dirname(__DIR__) . '/includes/init.php';
require_admin();

$editId = (int) request('id', 0);
$item = $editId ? db_query('SELECT * FROM events WHERE id=?', [$editId])->fetch() : null;

if (is_post()) {
    require_csrf();
    if (request('action') === 'delete') {
        db_query('DELETE FROM events WHERE id=?', [(int) request('delete_id')]);
        flash('success', t('admin.deleted'));
        redirect('admin/events.php');
    }
    $cover = $item['cover_image'] ?? null;
    try {
        $cover = save_upload('cover_image', 'events', $cover);
    } catch (RuntimeException $ex) {
        flash('error', $ex->getMessage());
        redirect('admin/events.php' . (isset($_GET['id']) ? '?id=' . $editId : ''));
    }
    $payload = [
        trim((string) request('title')),
        trim((string) request('description')),
        str_replace('T', ' ', (string) request('event_date')),
        trim((string) request('location')) ?: null,
        request('is_online') ? 1 : 0,
        trim((string) request('meeting_link')) ?: null,
        $cover,
    ];
    if ($item) {
        db_query(
            'UPDATE events SET title=?, description=?, event_date=?, location=?, is_online=?, meeting_link=?, cover_image=? WHERE id=?',
            [...$payload, $item['id']]
        );
    } else {
        db_query(
            'INSERT INTO events (title, description, event_date, location, is_online, meeting_link, cover_image) VALUES (?,?,?,?,?,?,?)',
            $payload
        );
    }
    flash('success', t('admin.saved'));
    redirect('admin/events.php');
}

$list = db_query('SELECT * FROM events ORDER BY event_date DESC')->fetchAll();
$adminPage = t('admin.events');
require dirname(__DIR__) . '/includes/admin_header.php';
$row = $item ?: ['title'=>'','description'=>'','event_date'=>date('Y-m-d\TH:i'),'location'=>'','is_online'=>0,'meeting_link'=>''];
?>
<h1><?= e(t('admin.events')) ?></h1>
<p><a class="btn btn-primary btn-sm" href="<?= e(url('admin/events.php?id=0')) ?>"><?= e(t('btn.add')) ?></a></p>
<?php if (isset($_GET['id'])): ?>
<form class="form card" method="post" enctype="multipart/form-data" style="margin-bottom:1.5rem">
  <?= csrf_field() ?>
  <div><label>শিরোনাম</label><input name="title" required value="<?= e($row['title']) ?>"></div>
  <div><label><?= e(t('label.date')) ?></label>
    <input type="datetime-local" name="event_date" required value="<?= e(date('Y-m-d\TH:i', strtotime((string)$row['event_date']))) ?>">
  </div>
  <div><label><?= e(t('label.location')) ?></label><input name="location" value="<?= e($row['location']) ?>"></div>
  <label><input type="checkbox" name="is_online" value="1" <?= !empty($row['is_online'])?'checked':'' ?>> <?= e(t('events.online')) ?></label>
  <div><label><?= e(t('events.link')) ?></label><input type="url" name="meeting_link" value="<?= e($row['meeting_link']) ?>"></div>
  <div><label>কভার</label><input type="file" name="cover_image" accept="image/jpeg,image/png,image/webp"></div>
  <div><label>বিবরণ</label><textarea name="description"><?= e($row['description']) ?></textarea></div>
  <button class="btn btn-primary"><?= e(t('btn.save')) ?></button>
</form>
<?php endif; ?>
<div class="table-wrap card">
  <table class="data">
    <thead><tr><th>শিরোনাম</th><th><?= e(t('label.date')) ?></th><th></th></tr></thead>
    <tbody>
    <?php foreach ($list as $ev): ?>
      <tr>
        <td><?= e($ev['title']) ?></td>
        <td><?= e(bn_date($ev['event_date'], 'd M Y, H:i')) ?></td>
        <td>
          <a href="<?= e(url('admin/events.php?id='.(int)$ev['id'])) ?>"><?= e(t('btn.edit')) ?></a>
          <form method="post" style="display:inline" data-confirm="<?= e(t('admin.confirm_delete')) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="delete_id" value="<?= (int)$ev['id'] ?>">
            <button class="btn btn-danger btn-sm"><?= e(t('btn.delete')) ?></button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php require dirname(__DIR__) . '/includes/admin_footer.php'; ?>
