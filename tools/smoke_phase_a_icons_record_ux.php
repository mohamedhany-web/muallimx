<?php

$css = file_get_contents(__DIR__.'/../public/css/classroom-meetline.css');
$blade = file_get_contents(__DIR__.'/../resources/views/student/classroom/room.blade.php');

$checks = [
    // Icons enlarged in later UX pass (52/21) — keep smoke aligned with classroom-meetline.css
    'icon_btn_52' => substr_count($css, 'width: 52px;') > 0 && substr_count($css, 'height: 52px;') > 0,
    'icon_font_21' => substr_count($css, 'font-size: 21px;') > 0,
    'dock_sep' => substr_count($css, '.mx-ml-dock-sep') > 0,
    'record_menu_head' => substr_count($css, '.mx-ml-record-menu-head') > 0,
    'share_hint' => substr_count($blade, 'mx-ml-share-hint') > 0,
    'share_title_no_record' => substr_count($blade, 'مشاركة الشاشة فقط — بدون تسجيل') > 0,
    'record_label_session' => substr_count($blade, 'تسجيل الجلسة') > 0,
    'record_menu_clarify' => substr_count($blade, 'بدون شير سكرين') > 0 || substr_count($blade, 'بدون مشاركة') > 0,
    'share_vs_record_copy' => substr_count($blade, 'شير سكرين = عرض للمشاركين فقط') > 0,
    'mxSetShareUi' => substr_count($blade, 'function mxSetShareUi') > 0,
    'share_icon_id' => substr_count($blade, 'id="mx-ml-share-icon"') > 0,
];

$fail = 0;
foreach ($checks as $k => $ok) {
    echo ($ok ? 'OK' : 'FAIL')." {$k}\n";
    if (! $ok) {
        $fail++;
    }
}

exit($fail > 0 ? 1 : 0);
