<?php
declare(strict_types=1);
require __DIR__ . '/includes/init.php';

$countryList = countries(true);
$errors = [];

if (is_post()) {
    require_csrf();
    if (rate_limited('volunteer', 6, 1800)) {
        $errors['form'] = t('err.rate_limit');
    } else {
        [$data, $errors] = require_post_fields(['name', 'email', 'interest_area']);
        $data['phone'] = trim((string) request('phone'));
        $data['message'] = trim((string) request('message'));
        $data['country_id'] = (int) request('country_id') ?: null;
        if ($data['email'] && !valid_email($data['email'])) {
            $errors['email'] = t('err.email');
        }
        if (!$errors) {
            rate_hit('volunteer');
            db_query(
                'INSERT INTO volunteer_interest (name, email, phone, country_id, interest_area, message) VALUES (?,?,?,?,?,?)',
                [$data['name'], $data['email'], $data['phone'] ?: null, $data['country_id'], $data['interest_area'], $data['message'] ?: null]
            );
            notify_admin('নতুন স্বেচ্ছাসেবক আগ্রহ', '<p>' . e($data['name']) . ' — ' . e($data['interest_area']) . '</p>');
            flash('success', t('volunteer.success'));
            redirect('get-involved.php');
        }
        flash_old($_POST);
    }
}

$meta = page_meta(t('volunteer.title'));
require __DIR__ . '/includes/header.php';
$ways = [
    ['volunteer.coordinator', 'নতুন দেশে চ্যাপ্টার গড়তে বা স্থানীয় সমন্বয় করতে চাইলে আগ্রহ জানান।'],
    ['volunteer.help', 'ইভেন্ট, অনুবাদ, হোয়াটসঅ্যাপ গ্রুপ দেখাশোনা — সময় দিয়ে সাহায্য।'],
    ['volunteer.donate', 'স্বেচ্ছায় অনুদান — জরুরি সহায়তায় কাজে লাগে।'],
    ['volunteer.stories', 'কীভাবে কেউ আপনাকে সাহায্য করেছে, সে গল্পটা লিখে পাঠান।'],
    ['volunteer.refer', 'অন্য প্রবাসী কর্মীকে এই নেটওয়ার্কের কথা বলুন।'],
];
?>
<section class="page-hero">
  <div class="container">
    <h1><?= e(t('volunteer.title')) ?></h1>
    <p><?= e(t('volunteer.intro')) ?></p>
  </div>
</section>
<section class="section">
  <div class="container split">
    <div class="grid">
      <?php foreach ($ways as $w): ?>
        <div class="card">
          <h3><?= e(t($w[0])) ?></h3>
          <p><?= e($w[1]) ?></p>
        </div>
      <?php endforeach; ?>
      <p><a class="btn btn-ghost" href="<?= e(url('donate.php')) ?>"><?= e(t('nav.donate')) ?></a></p>
    </div>
    <div class="card">
      <h2><?= e(t('volunteer.form')) ?></h2>
      <?php if (!empty($errors['form'])): ?><p class="field-error"><?= e($errors['form']) ?></p><?php endif; ?>
      <form class="form" method="post" data-validate>
        <?= csrf_field() ?>
        <div>
          <label for="name"><?= e(t('label.name')) ?></label>
          <input id="name" name="name" required value="<?= e(old('name')) ?>">
        </div>
        <div>
          <label for="email"><?= e(t('label.email')) ?></label>
          <input id="email" type="email" name="email" required value="<?= e(old('email')) ?>">
        </div>
        <div>
          <label for="phone"><?= e(t('label.phone')) ?></label>
          <input id="phone" name="phone" value="<?= e(old('phone')) ?>">
        </div>
        <div>
          <label for="country_id"><?= e(t('label.country')) ?></label>
          <select id="country_id" name="country_id">
            <option value="">—</option>
            <?php foreach ($countryList as $c): ?>
              <option value="<?= (int)$c['id'] ?>" <?= old('country_id') == $c['id'] ? 'selected' : '' ?>><?= e($c['name_bn']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label for="interest_area"><?= e(t('label.interest')) ?></label>
          <select id="interest_area" name="interest_area" required>
            <?php
            $areas = [
                'কান্ট্রি সমন্বয়ক',
                'স্বেচ্ছাসেবক',
                'অনুদান',
                'গল্প শেয়ার',
                'রেফার',
                'অন্যান্য',
            ];
            foreach ($areas as $a):
            ?>
              <option <?= old('interest_area') === $a ? 'selected' : '' ?>><?= e($a) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label for="message"><?= e(t('label.message')) ?></label>
          <textarea id="message" name="message"><?= e(old('message')) ?></textarea>
        </div>
        <button class="btn btn-primary" type="submit"><?= e(t('btn.submit')) ?></button>
      </form>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
