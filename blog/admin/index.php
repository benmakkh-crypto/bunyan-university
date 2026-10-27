<?php
/* لوحة الأخبار */
$NEWS = ['key' => 'news', 'table' => 'posts', 'cls' => 'classification', 'dir' => dirname(__DIR__) . '/img', 'url' => '/blog/img',
    'title' => 'أخبار الجامعة', 'sub' => 'انشر الأخبار والإعلانات وعدّلها، فتظهر فورًا في صفحة الأخبار والصفحة الرئيسية.',
    'front' => '/news.php', 'view' => '/blog/view.php?id=', 'noun' => 'الخبر', 'plural' => 'الأخبار', 'add' => 'خبر جديد'];
require dirname(__DIR__, 2) . '/inc/news_admin.php';
