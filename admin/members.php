<?php
declare(strict_types=1);
require dirname(__DIR__) . '/includes/init.php';
$adminUser = require_admin();

if (is_post()) {
    require_csrf();
    $id = (int) request('id');
    $action = (string) request('action');
    $member = db_query('SELECT * FROM members WHERE id=?', [$id])->fetch();
    if ($member && $action === 'approve') {
        $passwordPlain = null;
        $hash = $member['password_hash'];
        if (!$hash) {
            $passwordPlain = random_password(10);
            $hash = password_hash($passwordPlain, PASSWORD_DEFAULT);
        }
        db_query(
            "UPDATE members SET status='approved', password_hash=?, joined_at=NOW() WHERE id=?",
            [$hash, $id]
        );
        $body = '<p>আপনার REFA সদস্যপদ অনুমোদিত হয়েছে।</p><p>লগইন: <a href="' . e(url('login.php')) . '">' . e(url('login.php')) . '</a></p>';
        if ($passwordPlain) {
            $body .= '<p>অস্থায়ী পাসওয়ার্ড: <b>' . e($passwordPlain) . '</b></p><p>লগইনের পর পাসওয়ার্ড বদলান।</p>';
        }
        send_mail($member['email'], 'REFA সদস্যপদ অনুমোদিত', mail_template('স্বাগতম', $body));
        flash('success', t('admin.member_approved'));
    } elseif ($member && $action === 'reject') {
        db_query("UPDATE members SET status='rejected' WHERE id=?", [$id]);
        send_mail($member['email'], 'REFA সদস্যপদ', mail_template('আবেদন', '<p>দুঃখিত, আপনার আবেদন এখন গ্রহণ করা যায়নি। প্রয়োজনে যোগাযোগ করুন।</p>'));
        flash('success', t('admin.member_rejected'));
    } elseif ($member && $action === 'save') {
        db_query(
            'UPDATE members SET name=?, phone=?, whatsapp=?, country_id=?, profession=?, bio=?, membership_type=?, status=? WHERE id=?',
            [
                trim((string) request('name')),
                trim((string) request('phone')),
                trim((string) request('whatsapp')) ?: null,
                (int) request('country_id') ?: null,
                trim((string) request('profession')),
                trim((string) request('bio')),
                request('membership_type') === 'honorary' ? 'honorary' : 'general',
                in_array(request('status'), ['pending','approved','rejected'], true) ? request('status') : $member['status'],
                $id,
            ]
        );
        flash('success', t('admin.saved'));
    } elseif ($action === 'delete') {
        db_query('DELETE FROM members WHERE id=?', [$id]);
        flash('success', t('admin.deleted'));
    }
    redirect('admin/members.php');
}

$filter = (string) request('status', '');
$sql = 'SELECT m.*, c.name_bn FROM members m LEFT JOIN countries c ON c.id=m.country_id';
$params = [];
if (in_array($filter, ['pending','approved','rejected'], true)) {
    $sql .= ' WHERE m.status=?';
    $params[] = $filter;
}
$sql .= ' ORDER BY m.created_at DESC';
$list = db_query($sql, $params)->fetchAll();
$editId = (int) request('id', 0);
$edit = $editId ? db_query('SELECT * FROM members WHERE id=?', [$editId])->fetch() : null;
$countryList = countries(false);

$adminPage = t('admin.members');
require dirname(__DIR__) . '/includes/admin_header.php';
?>
<h1><?= e(t('admin.members')) ?></h1>
<p>
  <a href="<?= e(url('admin/members.php')) ?>">সব</a> ·
  <a href="<?= e(url('admin/members.php?status=pending')) ?>"><?= e(t('admin.pending')) ?></a> ·
  <a href="<?= e(url('admin/members.php?status=approved')) ?>"><?= e(t('admin.approved')) ?></a> ·
  <a href="<?= e(url('admin/members.php?status=rejected')) ?>"><?= e(t('admin.rejected')) ?></a>
</p>

<?php if ($edit): ?>
  <form class="form card" method="post" style="margin-bottom:1.5rem">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= (int)$edit['id'] ?>">
    <div class="grid grid-2">
      <div><label><?= e(t('label.name')) ?></label><input name="name" required value="<?= e($edit['name']) ?>"></div>
      <div><label><?= e(t('label.email')) ?></label><input value="<?= e($edit['email']) ?>" disabled></div>
      <div><label><?= e(t('label.phone')) ?></label><input name="phone" value="<?= e($edit['phone']) ?>"></div>
      <div><label><?= e(t('label.whatsapp')) ?></label><input name="whatsapp" value="<?= e($edit['whatsapp']) ?>"></div>
      <div>
        <label><?= e(t('label.country')) ?></label>
        <select name="country_id">
          <?php foreach ($countryList as $c): ?>
            <option value="<?= (int)$c['id'] ?>" <?= (int)$edit['country_id']===(int)$c['id']?'selected':'' ?>><?= e($c['name_bn']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div><label><?= e(t('label.profession')) ?></label><input name="profession" value="<?= e($edit['profession']) ?>"></div>
      <div>
        <label><?= e(t('label.membership_type')) ?></label>
        <select name="membership_type">
          <option value="general" <?= $edit['membership_type']==='general'?'selected':'' ?>><?= e(t('membership.general')) ?></option>
          <option value="honorary" <?= $edit['membership_type']==='honorary'?'selected':'' ?>><?= e(t('membership.honorary')) ?></option>
        </select>
      </div>
      <div>
        <label><?= e(t('label.status')) ?></label>
        <select name="status">
          <?php foreach (['pending','approved','rejected'] as $st): ?>
            <option value="<?= $st ?>" <?= $edit['status']===$st?'selected':'' ?>><?= e(t('admin.' . $st)) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div><label><?= e(t('label.bio')) ?></label><textarea name="bio"><?= e($edit['bio']) ?></textarea></div>
    <button class="btn btn-primary" type="submit"><?= e(t('btn.save')) ?></button>
  </form>
<?php endif; ?>

<div class="table-wrap card">
  <table class="data">
    <thead>
      <tr>
        <th><?= e(t('label.name')) ?></th>
        <th><?= e(t('label.email')) ?></th>
        <th><?= e(t('label.country')) ?></th>
        <th><?= e(t('label.status')) ?></th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($list as $m): ?>
        <tr>
          <td><?= e($m['name']) ?></td>
          <td><?= e($m['email']) ?></td>
          <td><?= e($m['name_bn'] ?? '') ?></td>
          <td><?= e($m['status']) ?></td>
          <td>
            <a href="<?= e(url('admin/members.php?id=' . (int)$m['id'])) ?>"><?= e(t('btn.edit')) ?></a>
            <?php if ($m['status']==='pending'): ?>
              <form method="post" style="display:inline">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                <input type="hidden" name="action" value="approve">
                <button class="btn btn-primary btn-sm" type="submit"><?= e(t('btn.approve')) ?></button>
              </form>
              <form method="post" style="display:inline">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                <input type="hidden" name="action" value="reject">
                <button class="btn btn-danger btn-sm" type="submit"><?= e(t('btn.reject')) ?></button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php require dirname(__DIR__) . '/includes/admin_footer.php'; ?>
