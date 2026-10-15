<?php
declare(strict_types=1);
require __DIR__ . '/includes/init.php';

$who = db_query("SELECT * FROM content_blocks WHERE block_key = 'home_who'")->fetch();
$chapters = countries(true);
$news = db_query(
    'SELECT * FROM news WHERE published_at IS NOT NULL AND published_at <= NOW() ORDER BY published_at DESC LIMIT 3'
)->fetchAll();
$events = db_query(
    'SELECT * FROM events WHERE event_date >= NOW() ORDER BY event_date ASC LIMIT 3'
)->fetchAll();
$stories = db_query(
    'SELECT t.*, c.name_bn AS country_name, c.iso_code FROM testimonials t
     LEFT JOIN countries c ON c.id = t.country_id
     WHERE t.is_featured = 1 ORDER BY t.created_at DESC LIMIT 3'
)->fetchAll();
$faqs = db_query('SELECT * FROM faqs WHERE is_active = 1 ORDER BY sort_order, id')->fetchAll();

$meta = page_meta(t('nav.home'), t('meta.home'));
require __DIR__ . '/includes/header.php';
?>

<section class="hero">
  <div class="container hero-inner">
    <div
      class="hero-lottie"
      data-hero-lottie="<?= e(url('hero.json')) ?>"
      role="img"
      aria-label="<?= e(t('site.name_bn')) ?>"
    ></div>
    <span class="hero-kicker">ফিনল্যান্ড · ২০২৬ · অলাভজনক কমিউনিটি</span>
    <h1><?= e(t('site.name_bn')) ?></h1>
    <p class="tagline"><?= e(t('site.tagline')) ?></p>
    <p class="lede"><?= e(t('site.short')) ?> — স্পেন, সৌদি আরব, যুক্তরাজ্য, কাতার ও আরও দেশে প্রবাসী ভাই-বোনেরা একে অপরের পাশে।</p>
    <div class="hero-actions">
      <a class="btn btn-accent" href="<?= e(url('membership.php')) ?>"><?= e(t('home.hero_cta_join')) ?></a>
      <a class="btn btn-light" href="<?= e(url('about.php')) ?>"><?= e(t('home.hero_cta_about')) ?></a>
    </div>
  </div>
</section>

<section class="section">
  <div class="container split">
    <div class="prose">
      <p class="kicker"><?= e(t('home.who_title')) ?></p>
      <h2><?= e($who['title'] ?? t('home.who_title')) ?></h2>
      <?= nl2p($who['body'] ?? '') ?>
      <a class="btn btn-ghost" href="<?= e(url('about.php')) ?>"><?= e(t('btn.learn_more')) ?></a>
    </div>
    <div class="card">
      <h3>ফিনল্যান্ড থেকে বিশ্বে</h3>
      <p>সদর দপ্তর হেলসিঙ্কিতে। চ্যাপ্টারগুলো ডাটাবেজ থেকে আসে — নতুন দেশ যোগ হলে সাইটে নিজে থেকে দেখাবে।</p>
      <p class="muted">এটা কর্মীদের নেটওয়ার্ক, প্রতিষ্ঠানের বিলবোর্ড নয়।</p>
    </div>
  </div>
</section>

<section class="section" style="padding-top:0">
  <div class="container">
    <div class="section-head">
      <div>
        <p class="kicker"><?= e(t('home.reach_title')) ?></p>
        <h2><?= e(t('home.reach_title')) ?></h2>
        <p><?= e(t('home.reach_text')) ?></p>
      </div>
      <a href="<?= e(url('chapters.php')) ?>"><?= e(t('home.view_all')) ?></a>
    </div>

    <div class="reach-visual" aria-hidden="true">
      <svg viewBox="0 0 800 220" role="img">
        <ellipse cx="400" cy="110" rx="300" ry="88" fill="none" stroke="#7dcead" stroke-width="1.5" opacity="0.5"/>
        <ellipse cx="400" cy="110" rx="180" ry="88" fill="none" stroke="#7dcead" stroke-width="1" opacity="0.35"/>
        <line x1="100" y1="110" x2="700" y2="110" stroke="#7dcead" opacity="0.25"/>
        <circle cx="390" cy="70" r="7" fill="#d4a24a"/>
        <circle cx="250" cy="100" r="6" fill="#e07a5a"/>
        <circle cx="320" cy="130" r="6" fill="#e07a5a"/>
        <circle cx="430" cy="95" r="6" fill="#7dcead"/>
        <circle cx="470" cy="140" r="6" fill="#e07a5a"/>
        <circle cx="520" cy="110" r="6" fill="#7dcead"/>
        <circle cx="560" cy="125" r="6" fill="#d4a24a"/>
        <circle cx="610" cy="100" r="6" fill="#7dcead"/>
        <circle cx="200" cy="90" r="6" fill="#7dcead"/>
      </svg>
      <div class="reach-dots">
        <?php foreach ($chapters as $c): ?>
          <span><?= flag_emoji($c['iso_code']) ?> <?= e($c['name_bn']) ?></span>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="grid grid-4">
      <?php foreach ($chapters as $c): ?>
        <a class="card card-link country-card" href="<?= e(url('chapter.php?slug=' . urlencode($c['slug']))) ?>">
          <div class="flag"><?= flag_emoji($c['iso_code']) ?></div>
          <h3><?= e($c['name_bn']) ?></h3>
          <p class="card-meta"><?= e($c['name_en']) ?></p>
          <?php if ($c['is_hq']): ?><span class="badge"><?= e(t('home.hq_badge')) ?></span><?php endif; ?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section" style="padding-top:0">
  <div class="container">
    <div class="section-head">
      <div>
        <p class="kicker"><?= e(t('home.values_title')) ?></p>
        <h2><?= e(t('home.values_title')) ?></h2>
      </div>
    </div>
    <div class="grid grid-4 values">
      <?php
      $values = [
        ['সহ', 'value.cooperation'],
        ['সং', 'value.solidarity'],
        ['স্ব', 'value.transparency'],
        ['সে', 'value.service'],
      ];
      foreach ($values as $v):
      ?>
      <div class="card">
        <div class="value-icon"><?= e($v[0]) ?></div>
        <h3><?= e(t($v[1])) ?></h3>
        <p><?= e(t($v[1] . '_text')) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section" style="padding-top:0">
  <div class="container">
    <div class="section-head">
      <div>
        <p class="kicker"><?= e(t('nav.news')) ?></p>
        <h2><?= e(t('home.news_title')) ?></h2>
      </div>
      <a href="<?= e(url('news.php')) ?>"><?= e(t('home.view_all')) ?></a>
    </div>
    <div class="grid grid-3">
      <?php foreach ($news as $item): ?>
        <a class="card card-link list-card" href="<?= e(url('news-single.php?slug=' . urlencode($item['slug']))) ?>">
          <div class="cover"></div>
          <p class="card-meta"><?= e($item['category']) ?> · <?= e(bn_date($item['published_at'])) ?></p>
          <h3><?= e($item['title']) ?></h3>
          <p><?= e(excerpt($item['body'], 110)) ?></p>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section" style="padding-top:0">
  <div class="container split">
    <div>
      <div class="section-head">
        <div>
          <p class="kicker"><?= e(t('nav.events')) ?></p>
          <h2><?= e(t('home.events_title')) ?></h2>
        </div>
        <a href="<?= e(url('events.php')) ?>"><?= e(t('home.view_all')) ?></a>
      </div>
      <div class="grid">
        <?php foreach ($events as $ev): ?>
          <a class="card card-link" href="<?= e(url('event.php?id=' . (int)$ev['id'])) ?>">
            <p class="card-meta"><?= e(bn_date($ev['event_date'], 'd M Y, H:i')) ?> · <?= $ev['is_online'] ? e(t('events.online')) : e($ev['location']) ?></p>
            <h3><?= e($ev['title']) ?></h3>
            <p><?= e(excerpt($ev['description'] ?? '', 120)) ?></p>
          </a>
        <?php endforeach; ?>
        <?php if (!$events): ?><p class="muted"><?= e(t('events.empty')) ?></p><?php endif; ?>
      </div>
    </div>
    <div>
      <div class="section-head">
        <div>
          <p class="kicker"><?= e(t('nav.stories')) ?></p>
          <h2><?= e(t('home.stories_title')) ?></h2>
        </div>
        <a href="<?= e(url('stories.php')) ?>"><?= e(t('home.view_all')) ?></a>
      </div>
      <div class="grid">
        <?php foreach ($stories as $s): ?>
          <article class="card story-card">
            <p><?= e(excerpt($s['story'], 180)) ?></p>
            <p class="card-meta"><?= e($s['member_name']) ?> · <?= flag_emoji($s['iso_code'] ?? '') ?> <?= e($s['country_name'] ?? '') ?></p>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<?php if ($faqs): ?>
<section class="section" style="padding-top:0">
  <div class="container">
    <h2><?= e(t('home.faq_title')) ?></h2>
    <div class="faq">
      <?php foreach ($faqs as $f): ?>
        <details>
          <summary><?= e($f['question']) ?></summary>
          <p><?= e($f['answer']) ?></p>
        </details>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="section" style="padding-top:0">
  <div class="container">
    <div class="cta-banner">
      <div>
        <h2><?= e(t('home.cta_title')) ?></h2>
        <p><?= e(t('home.cta_text')) ?></p>
      </div>
      <a class="btn btn-accent" href="<?= e(url('membership.php')) ?>"><?= e(t('btn.join')) ?></a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
