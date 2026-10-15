  </main>
  <footer class="site-footer">
    <div class="container footer-grid">
      <div>
        <a class="brand brand-footer" href="<?= e(url('index.php')) ?>">
          <img class="brand-logo brand-logo-footer" src="<?= e(asset('images/logo.png')) ?>" alt="<?= e(t('site.name')) ?> — <?= e(t('site.name_bn')) ?>" width="152" height="66">
        </a>
        <p class="footer-tagline"><?= e(t('site.tagline')) ?></p>
        <p class="muted"><?= e(t('footer.nonprofit')) ?></p>
        <p class="muted"><?= e(t('footer.hq')) ?></p>
        <div class="social">
          <?php if (setting('facebook_url')): ?>
            <a href="<?= e(setting('facebook_url')) ?>" rel="noopener noreferrer" target="_blank" aria-label="Facebook">f</a>
          <?php endif; ?>
          <?php if (setting('whatsapp_url')): ?>
            <a href="<?= e(setting('whatsapp_url')) ?>" rel="noopener noreferrer" target="_blank" aria-label="WhatsApp">W</a>
          <?php endif; ?>
          <a href="<?= e(url('contact.php')) ?>" aria-label="<?= e(t('nav.contact')) ?>">@</a>
        </div>
      </div>
      <div>
        <h2><?= e(t('footer.quick')) ?></h2>
        <ul class="footer-links">
          <li><a href="<?= e(url('about.php')) ?>"><?= e(t('nav.about')) ?></a></li>
          <li><a href="<?= e(url('chapters.php')) ?>"><?= e(t('nav.chapters')) ?></a></li>
          <li><a href="<?= e(url('membership.php')) ?>"><?= e(t('nav.membership')) ?></a></li>
          <li><a href="<?= e(url('get-involved.php')) ?>"><?= e(t('nav.volunteer')) ?></a></li>
          <li><a href="<?= e(url('donate.php')) ?>"><?= e(t('nav.donate')) ?></a></li>
          <li><a href="<?= e(url('resources.php')) ?>"><?= e(t('nav.resources')) ?></a></li>
        </ul>
      </div>
      <div>
        <h2><?= e(t('footer.contact')) ?></h2>
        <p><?= e(setting('hq_address', t('footer.hq'))) ?></p>
        <p><a href="mailto:<?= e(setting('hq_email', 'info@refa.org')) ?>"><?= e(setting('hq_email', 'info@refa.org')) ?></a></p>
        <p><?= e(setting('hq_phone', '')) ?></p>
      </div>
      <div>
        <h2><?= e(t('footer.newsletter')) ?></h2>
        <p><?= e(t('footer.newsletter_text')) ?></p>
        <form class="newsletter-form" method="post" action="<?= e(url('subscribe.php')) ?>">
          <?= csrf_field() ?>
          <label class="sr-only" for="nl-email"><?= e(t('label.email')) ?></label>
          <input id="nl-email" type="email" name="email" required placeholder="<?= e(t('footer.email_placeholder')) ?>">
          <button class="btn btn-accent" type="submit"><?= e(t('footer.subscribe')) ?></button>
        </form>
      </div>
    </div>
    <div class="container footer-bottom">
      <p>© <?= date('Y') ?> REFA · <?= e(t('footer.rights')) ?></p>
    </div>
  </footer>
  <script src="<?= e(asset('js/app.js')) ?>"></script>
</body>
</html>
