<?php

$root = dirname(__DIR__);
$js = $root.'/public/js/classroom-virtual-background.js';
$css = $root.'/public/css/classroom-meetline.css';
$room = $root.'/resources/views/student/classroom/room.blade.php';
$join = $root.'/resources/views/classroom/join.blade.php';

$presets = [
    'soft-blue.svg', 'fresh-green.svg', 'violet-dusk.svg', 'warm-sunset.svg',
    'classroom-board.svg', 'office-light.svg', 'ocean-wave.svg', 'soft-rose.svg',
];

$fail = 0;
function ok($cond, $label) {
    global $fail;
    echo ($cond ? 'OK' : 'FAIL')." {$label}\n";
    if (! $cond) {
        $fail++;
    }
}

ok(is_readable($js), 'vbg_js');
ok(is_readable($css) && str_contains(file_get_contents($css), '#mx-vbg-panel'), 'vbg_css');
$roomSrc = file_get_contents($room);
$joinSrc = file_get_contents($join);
ok(str_contains($roomSrc, 'mx-ml-btn-bg'), 'room_btn');
ok(str_contains($roomSrc, 'mx-classroom-vbg-js'), 'room_inline_js');
ok(str_contains($roomSrc, 'disableVirtualBackground: false'), 'room_config');
ok(str_contains($joinSrc, 'mx-ml-btn-bg'), 'join_btn');
ok(str_contains($joinSrc, 'mx-classroom-vbg-js'), 'join_inline_js');
ok(str_contains($joinSrc, 'disableVirtualBackground: false'), 'join_config');

foreach ($presets as $p) {
    ok(is_readable($root.'/public/images/classroom-backgrounds/'.$p), 'preset_'.$p);
}

$jsSrc = file_get_contents($js);
ok(str_contains($jsSrc, 'setVirtualBackground'), 'api_setVirtualBackground');
ok(str_contains($jsSrc, 'setBlurredBackground'), 'api_setBlurredBackground');
ok(str_contains($jsSrc, 'bindUi'), 'bindUi');

exit($fail > 0 ? 1 : 0);
