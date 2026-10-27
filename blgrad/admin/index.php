<?php
/* لوحة أخبار الخريجين وحفلات التخرج */
$NEWS = ['key' => 'events', 'table' => 'postgrad', 'cls' => 'class', 'dir' => dirname(__DIR__) . '/img', 'url' => '/blgrad/img',
    'title' => 'أخبار الخريجين والفعاليات', 'sub' => 'انشر أخبار الخريجين وحفلات التخرج، فتظهر في صفحة وحدة الخريجين.',
    'front' => '/grad.php', 'view' => '/blgrad/view.php?id=', 'noun' => 'الخبر', 'plural' => 'الأخبار', 'add' => 'خبر جديد'];
require dirname(__DIR__, 2) . '/inc/news_admin.php';
