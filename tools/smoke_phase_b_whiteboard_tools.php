<?php

$js = file_get_contents(__DIR__.'/../public/js/classroom-wb-tools.js');
$css = file_get_contents(__DIR__.'/../public/css/classroom-meetline.css');
$room = file_get_contents(__DIR__.'/../resources/views/student/classroom/room.blade.php');
$join = file_get_contents(__DIR__.'/../resources/views/classroom/join.blade.php');

$checks = [
    'js_module' => str_contains($js, 'MxClassroomWbTools'),
    'js_bind' => str_contains($js, 'bindToolbar'),
    'js_pen' => str_contains($js, "'freedraw'"),
    'js_text' => str_contains($js, "'text'"),
    'js_eraser' => str_contains($js, "'eraser'"),
    'js_clear' => str_contains($js, 'clearAll'),
    'css_tools' => str_contains($css, '.mx-wb-tools'),
    'css_tool_btn' => str_contains($css, '.mx-wb-tool-btn'),
    'room_inline_js' => str_contains($room, 'mx-classroom-wb-tools-js'),
    'room_tools_host' => str_contains($room, 'mx-wb-tools-host'),
    'room_shared_title' => str_contains($room, 'سبورة مشتركة'),
    'room_bind_tools' => str_contains($room, 'MxClassroomWbTools.bindToolbar'),
    'join_inline_js' => str_contains($join, 'mx-classroom-wb-tools-js'),
    'join_tools_guest' => str_contains($join, 'mx-wb-tools-guest'),
    'join_shared_copy' => str_contains($join, 'السبورة المشتركة'),
    'join_bind_tools' => str_contains($join, 'MxClassroomWbTools.bindToolbar'),
    'join_pen_btn' => str_contains($join, 'قلم السبورة'),
];

$fail = 0;
foreach ($checks as $k => $ok) {
    echo ($ok ? 'OK' : 'FAIL')." {$k}\n";
    if (! $ok) {
        $fail++;
    }
}

exit($fail > 0 ? 1 : 0);
