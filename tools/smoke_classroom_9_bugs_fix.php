<?php

$css = file_get_contents(__DIR__.'/../public/css/classroom-meetline.css');
$noise = file_get_contents(__DIR__.'/../public/js/classroom-noise-isolation.js');
$share = file_get_contents(__DIR__.'/../public/js/classroom-share-controls.js');
$room = file_get_contents(__DIR__.'/../resources/views/student/classroom/room.blade.php');
$join = file_get_contents(__DIR__.'/../resources/views/classroom/join.blade.php');

$checks = [
    'icons_52' => str_contains($css, 'width: 52px') && str_contains($css, 'height: 52px'),
    'icons_font_21' => str_contains($css, 'font-size: 21px'),
    'share_aec' => str_contains($share, 'disableAEC'),
    'share_preserve_sharing_arg' => str_contains($share, 'preserveReceiveAudio(api, sharing)') || preg_match('/function preserveReceiveAudio\(api,\s*sharing\)/', $share),
    'room_close_pip_on_share' => str_contains($room, 'mxCloseParticipantsPip()'),
    'room_tab_audio_toast' => str_contains($room, 'صوت التبويب'),
    'room_fallback_quiet' => str_contains($room, 'التسجيل يعمل بالمسار الاحتياطي'),
    'room_leave_toast' => str_contains($room, "mxToast('غادر:"),
    'room_mic_hint' => str_contains($room, 'الميكروفون مكتوم'),
    'noise_mark_joined' => str_contains($noise, 'markJoined'),
    'noise_no_prejoin_error' => str_contains($noise, 'سيُفعَّل بعد دخول الغرفة'),
    'join_opts_menu' => str_contains($join, 'mx-guest-opts-panel'),
    'join_zoom_in_opts' => str_contains($join, 'data-zoom-in') && str_contains($join, 'mx-guest-opts-panel'),
    'join_hb_3s' => str_contains($join, '}, 3000);'),
    'join_wb_mount_error' => str_contains($join, 'تعذّر فتح السبورة'),
    'join_wb_toast' => str_contains($join, 'تم إتاحة الكتابة') || str_contains($join, 'تم إتاحة السبورة'),
    'join_wb_view_always' => str_contains($join, 'showGuestWbButtonAfterJoin'),
    'join_no_toggle_share_deny' => ! preg_match('/toggleShareScreen,\s*false/', $join),
    'tools_empty_hint' => str_contains($css, 'جاري تحميل أدوات السبورة'),
    'participants_panel' => str_contains($room, 'mx-participants-panel'),
    'end_no_force_inpage' => ! preg_match('/forceInPage\s*=\s*!!opts\.forceInPage\s*\|\|\s*!!pendingEndMeetingSubmit/', $room),
];

$fail = 0;
foreach ($checks as $k => $ok) {
    echo ($ok ? 'OK' : 'FAIL')." {$k}\n";
    if (! $ok) {
        $fail++;
    }
}

exit($fail > 0 ? 1 : 0);
