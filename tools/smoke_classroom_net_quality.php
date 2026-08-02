<?php

$js = file_get_contents(__DIR__.'/../public/js/classroom-net-quality.js');
$room = file_get_contents(__DIR__.'/../resources/views/student/classroom/room.blade.php');
$join = file_get_contents(__DIR__.'/../resources/views/classroom/join.blade.php');
$css = file_get_contents(__DIR__.'/../public/css/classroom-meetline.css');

$checks = [
    'js_module' => str_contains($js, 'MxClassroomNetQuality'),
    'js_should_blame' => str_contains($js, 'shouldBlameNetwork'),
    'js_browser_hint' => str_contains($js, 'browserNetHint'),
    'js_sustained_poor' => str_contains($js, 'poorMs'),
    'js_msg_ar' => str_contains($js, 'اتصال الإنترنت غير مستقر'),
    'js_strong_skip' => str_contains($js, "hint === 'strong'"),
    'css_banner' => str_contains($css, '#mx-net-quality-banner'),
    'room_banner' => str_contains($room, 'mx-net-quality-banner'),
    'room_inline_js' => str_contains($room, 'mx-classroom-net-quality-js'),
    'room_monitor' => str_contains($room, '__mxNetQualityMonitor'),
    'join_banner' => str_contains($join, 'mx-net-quality-banner'),
    'join_inline_js' => str_contains($join, 'mx-classroom-net-quality-js'),
    'join_cq_listener' => str_contains($join, 'connectionQualityChanged'),
];

$fail = 0;
foreach ($checks as $k => $ok) {
    echo ($ok ? 'OK' : 'FAIL')." {$k}\n";
    if (! $ok) {
        $fail++;
    }
}

exit($fail > 0 ? 1 : 0);
