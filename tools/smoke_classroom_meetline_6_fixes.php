<?php

/**
 * Regression guards for Classroom Meet.Line six fixes:
 * 1) whiteboard tools bar  2) participants panel  3) guest whiteboard view
 * 4) screen-share FPS      5+6) upload tab + immediate end meeting
 */

$jsTools = file_get_contents(__DIR__.'/../public/js/classroom-wb-tools.js');
$jsNoise = file_get_contents(__DIR__.'/../public/js/classroom-noise-isolation.js');
$css = file_get_contents(__DIR__.'/../public/css/classroom-meetline.css');
$jitsiCss = file_get_contents(__DIR__.'/../public/css/jitsi-meetline-theme.css');
$room = file_get_contents(__DIR__.'/../resources/views/student/classroom/room.blade.php');
$join = file_get_contents(__DIR__.'/../resources/views/classroom/join.blade.php');
$ctrl = file_get_contents(__DIR__.'/../app/Http/Controllers/ClassroomJoinController.php');

$checks = [
    // 1 — tools
    'tools_bind_when_ready' => str_contains($jsTools, 'bindToolbarWhenReady'),
    'room_ensure_host_tools' => str_contains($room, 'mxEnsureHostWbTools'),
    'room_tools_error_visible' => str_contains($room, 'mxShowWbToolsError'),
    'room_wb_open_title' => str_contains($room, 'فتح السبورة وأدوات القلم'),

    // 2 — participants
    'room_toolbar_has_participants_pane' => str_contains($room, "'participants-pane'"),
    'room_custom_participants_panel' => str_contains($room, 'mx-participants-panel'),
    'room_toggle_participants_ui' => str_contains($room, 'mxToggleParticipantsUi'),
    'css_participants_panel' => str_contains($css, '.mx-participants-panel'),
    'jitsi_hide_toolbox' => str_contains($jitsiCss, '.new-toolbox'),
    // Must NOT force empty toolbarButtons that break pane commands
    'room_no_empty_toolbar_buttons' => ! preg_match('/toolbarButtons:\s*\[\s*\]/', $room),

    // 3 — guest whiteboard view-only
    'join_show_wb_after_join' => str_contains($join, 'showGuestWbButtonAfterJoin'),
    'join_view_mode' => str_contains($join, 'viewModeEnabled: !guestWbAllowed'),
    'join_write_toast' => str_contains($join, 'تم إتاحة الكتابة'),
    'ctrl_wb_get_no_require_write' => str_contains($ctrl, 'resolveLiveMeetingForGuestWb($request, $code, false)'),
    'ctrl_wb_push_require_write' => str_contains($ctrl, 'resolveLiveMeetingForGuestWb($request, $code, true)'),
    'ctrl_allow_write_payload' => str_contains($ctrl, "'allow_write' => \$meeting->allowsParticipantWhiteboard()"),

    // 4 — share quality (no CPU-core gate; crisp share up to 30fps / 3.5Mbps)
    'noise_no_cpu_core_gate' => ! preg_match('/hardwareConcurrency/', $jsNoise),
    'noise_share_fps_30' => str_contains($jsNoise, 'max: 30'),
    'noise_share_comment' => str_contains($jsNoise, 'Force crisp durable screen share') || str_contains($jsNoise, 'crisp durable'),
    'noise_no_layer_suspension' => str_contains($jsNoise, 'enableLayerSuspension: false'),
    'noise_p2p_off' => str_contains($jsNoise, 'p2p: { enabled: false }'),
    'noise_sshigh_35' => str_contains($jsNoise, '3500000'),
    'noise_adaptive_off' => str_contains($jsNoise, 'enableAdaptiveMode: false'),
    'room_share_sshigh' => str_contains($room, 'ssHigh: 3500000'),
    'room_share_fps_floor' => str_contains($room, 'desktopSharingFrameRate: { min: 24, max: 30 }'),

    // 5+6 — upload tab + immediate end (do not forceInPage on end)
    'room_no_force_inpage_on_pending_end' => ! preg_match('/forceInPage\s*=\s*!!opts\.forceInPage\s*\|\|\s*!!pendingEndMeetingSubmit/', $room),
    'room_prefer_tab_only' => str_contains($room, 'preferTabOnly'),
    'room_end_submit_immediate' => str_contains($room, 'endMeetingForm.submit();')
        && ! preg_match('/setTimeout\(function\s*\(\)\s*\{\s*endMeetingForm\.submit\(\);\s*\},\s*450\)/', $room),
    'room_no_wait_upload_on_end' => ! str_contains($room, 'سيتم إنهاء الاجتماع تلقائياً بعد اكتماله'),
    'room_end_while_recording_only' => str_contains($room, 'if (!isRecording) return;'),
    'room_end_even_if_rec_invalid' => str_contains($room, 'Still end the meeting if the user already confirmed end'),
    'join_react_root_unmount' => str_contains($join, 'guestExcReactRoot') && str_contains($join, '.unmount()'),
];

$fail = 0;
foreach ($checks as $k => $ok) {
    echo ($ok ? 'OK' : 'FAIL')." {$k}\n";
    if (! $ok) {
        $fail++;
    }
}

exit($fail > 0 ? 1 : 0);
