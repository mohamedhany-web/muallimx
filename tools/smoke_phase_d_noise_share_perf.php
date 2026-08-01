<?php

$js = file_get_contents(__DIR__.'/../public/js/classroom-noise-isolation.js');
$wbSync = file_get_contents(__DIR__.'/../resources/views/partials/mx-classroom-wb-sync.blade.php');
$room = file_get_contents(__DIR__.'/../resources/views/student/classroom/room.blade.php');
$join = file_get_contents(__DIR__.'/../resources/views/classroom/join.blade.php');

$checks = [
    'js_reattach_fn' => str_contains($js, 'reattachNoiseAfterTrackChange'),
    'js_share_hook' => str_contains($js, 'onScreenShareChanged'),
    'js_channel_lastn' => str_contains($js, 'channelLastN'),
    'js_desktop_fps' => str_contains($js, 'desktopSharingFrameRate'),
    // Peak mode: 15fps on weak nets, 24fps otherwise (CPU-friendly on 2-vCPU hosts)
    'js_desktop_fps_high' => str_contains($js, 'max: save ? 15 : 24'),
    'js_no_cpu_share_gate' => ! preg_match('/hardwareConcurrency/', $js),
    'js_stereo_off' => str_contains($js, 'stereo: false'),
    'js_save_bw' => str_contains($js, 'prefersSaveBandwidth'),
    'wb_idle_poll' => str_contains($wbSync, 'idlePollMs'),
    'wb_set_active' => str_contains($wbSync, 'setActive'),
    'room_share_noise' => str_contains($room, 'onScreenShareChanged'),
    'room_wb_idle' => str_contains($room, 'idlePollMs'),
    'room_wb_set_active' => str_contains($room, 'hostWbSync.setActive'),
    'join_share_noise' => str_contains($join, 'onScreenShareChanged'),
    'join_wb_idle' => str_contains($join, 'idlePollMs'),
    'join_wb_set_active' => str_contains($join, 'sync.setActive'),
];

$fail = 0;
foreach ($checks as $k => $ok) {
    echo ($ok ? 'OK' : 'FAIL')." {$k}\n";
    if (! $ok) {
        $fail++;
    }
}

// Regression: prior noise smoke essentials
$reg = [
    'reg_noise_module' => str_contains($js, 'MxClassroomNoiseIsolation'),
    'reg_room_noise_btn' => str_contains($room, 'mx-ml-btn-noise'),
    'reg_join_noise_btn' => str_contains($join, 'mx-ml-btn-noise'),
];
foreach ($reg as $k => $ok) {
    echo ($ok ? 'OK' : 'FAIL')." {$k}\n";
    if (! $ok) {
        $fail++;
    }
}

exit($fail > 0 ? 1 : 0);
