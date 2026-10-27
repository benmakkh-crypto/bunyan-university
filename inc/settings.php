<?php
/*
 * إعدادات قابلة للتعديل من لوحة المشرف (your-domain.com/admin/settings)
 * تُحفظ في _private/settings.json (مقفل عن الإنترنت)
 */
if (defined('UWCIV_SETTINGS')) return;
define('UWCIV_SETTINGS', 1);

function uwciv_settings_path() { return dirname(__DIR__) . '/_private/settings.json'; }

function uwciv_settings()
{
    static $s = null;
    if ($s !== null) return $s;
    $s = ['social' => [], 'smtp_pass' => '', 'admin_hash' => '', 'calendar' => null, 'year' => ''];
    $f = uwciv_settings_path();
    if (is_file($f)) {
        $j = json_decode((string) @file_get_contents($f), true);
        if (is_array($j)) $s = array_merge($s, $j);
    }
    return $s;
}

function uwciv_settings_save(array $new)
{
    $f = uwciv_settings_path();
    if (!is_dir(dirname($f))) @mkdir(dirname($f), 0755, true);
    $ok = @file_put_contents($f, json_encode($new, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX) !== false;
    return $ok;
}

/* شبكات التواصل: المفتاح => [الاسم, الأيقونة] */
function uwciv_social_defs()
{
    return [
        'youtube'   => ['YouTube', 'fab fa-youtube'],
        'x'         => ['X / Twitter', 'fab fa-twitter'],
        'facebook'  => ['Facebook', 'fab fa-facebook-f'],
        'instagram' => ['Instagram', 'fab fa-instagram'],
        'linkedin'  => ['LinkedIn', 'fab fa-linkedin-in'],
        'whatsapp'  => ['WhatsApp', 'fab fa-whatsapp'],
        'telegram'  => ['Telegram', 'fab fa-telegram-plane'],
        'tiktok'    => ['TikTok', 'fab fa-tiktok'],
        'snapchat'  => ['Snapchat', 'fab fa-snapchat-ghost'],
    ];
}

/* رقم التواصل والموقع (يُعدّلان من صفحة الإعدادات) */
function uwciv_contact()
{
    $s = uwciv_settings();
    $phone = trim((string) ($s['phone'] ?? '')) ?: '+6282128000100';
    $digits = preg_replace('/\D/', '', $phone);
    if (preg_match('/^880(\d{4})(\d{6})$/', $digits, $m)) $pretty = '+880 ' . $m[1] . '-' . $m[2];
    elseif (preg_match('/^62(\d{3})(\d{4})(\d{3,5})$/', $digits, $m)) $pretty = '+62 ' . $m[1] . '-' . $m[2] . '-' . $m[3];
    else $pretty = $phone;
    $address = trim((string) ($s['address'] ?? '')) ?: 'ساجيرا، محافظة ليباك، بانتن، إندونيسيا';
    $loc = trim((string) ($s['location'] ?? '')) ?: 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode('Sajira, Lebak, Banten, Indonesia');
    // استخراج الإحداثيات من رابط خرائط Google لعرض الخريطة داخل الصفحة؛ وإن لم توجد تُعرض الخريطة بالعنوان
    $lat = null; $lng = null;
    if (preg_match('/!3d(-?\d+\.\d+)!4d(-?\d+\.\d+)/', $loc, $m) || preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $loc, $m) || preg_match('/[?&]q=(-?\d+\.\d+),(-?\d+\.\d+)/', $loc, $m)) { $lat = (float) $m[1]; $lng = (float) $m[2]; }
    $embed = $lat !== null ? 'https://maps.google.com/maps?q=' . $lat . ',' . $lng . '&z=16&hl=ar&output=embed'
                           : 'https://maps.google.com/maps?q=' . rawurlencode('Sajira, Lebak, Banten, Indonesia') . '&z=12&hl=ar&output=embed';
    return ['phone' => $phone, 'phone2' => '+6282128000200', 'digits' => $digits, 'pretty' => $pretty, 'location' => $loc, 'lat' => $lat, 'lng' => $lng, 'embed' => $embed, 'address' => $address];
}
