<?php
/* خريطة الموقع لمحركات البحث — تُولَّد تلقائيًا */
require_once __DIR__ . '/inc/colleges.php';
require_once __DIR__ . '/inc/pages.php';
header('Content-Type: application/xml; charset=utf-8');
$B = 'https://your-domain.com';
$today = date('Y-m-d');
$urls = [
  ['/', '1.0', 'daily'], ['/colleges', '0.9', 'weekly'], ['/student/index.php', '0.9', 'monthly'], ['/structure', '0.8', 'monthly'],
  ['/about.php', '0.8', 'monthly'], ['/edusys.php', '0.7', 'monthly'], ['/teacher/index.php', '0.7', 'monthly'], ['/calendar.php', '0.7', 'weekly'],
  ['/news.php', '0.8', 'daily'], ['/events.php', '0.6', 'weekly'], ['/images.php', '0.5', 'monthly'], ['/contact.php', '0.7', 'yearly'], ['/app', '0.7', 'monthly'], ['/regulations', '0.8', 'monthly'], ['/jobs', '0.7', 'weekly'], ['/journal', '0.7', 'monthly'],
  ['/ask.php', '0.6', 'monthly'], ['/elctro.php', '0.6', 'monthly'], ['/depl.php', '0.7', 'monthly'], ['/depm.php', '0.7', 'monthly'],
  ['/depmst.php', '0.7', 'monthly'], ['/depdec.php', '0.7', 'monthly'], ['/education.php', '0.6', 'monthly'], ['/research.php', '0.6', 'monthly'],
  ['/place.php', '0.5', 'yearly'], ['/countries.php', '0.5', 'yearly'], ['/stratj.php', '0.5', 'yearly'], ['/grad.php', '0.5', 'monthly'],
  ['/librarysystem/index.php', '0.6', 'weekly'], ['/pdf_grad/index.php', '0.5', 'monthly'], ['/onlineexam/index.php', '0.4', 'monthly'], ['/de/des.php', '0.4', 'monthly'],
];
foreach (array_keys($GLOBALS['UWCIV_COLLEGES']) as $slug) $urls[] = ['/college/' . $slug, '0.8', 'monthly'];
if (isset($UWCIV_PAGES) && is_array($UWCIV_PAGES)) foreach (array_keys($UWCIV_PAGES) as $k) if (!in_array($k, ['regs', 'jobs', 'journal'], true)) $urls[] = ['/info.php?p=' . $k, '0.5', 'monthly'];
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as $u) {
    echo '  <url><loc>' . htmlspecialchars($B . $u[0], ENT_XML1) . '</loc><lastmod>' . $today . '</lastmod><changefreq>' . $u[2] . '</changefreq><priority>' . $u[1] . "</priority></url>\n";
}
echo '</urlset>';
