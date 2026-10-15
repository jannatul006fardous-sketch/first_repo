<?php
declare(strict_types=1);
require dirname(__DIR__) . '/includes/init.php';
$member = require_member();
$chapter = $member['country_id']
    ? db_query('SELECT * FROM countries WHERE id = ?', [$member['country_id']])->fetch()
    : null;
if ($chapter) {
    redirect('chapter.php?slug=' . urlencode($chapter['slug']));
}
flash('info', t('member.no_chapter'));
redirect('member/dashboard.php');
