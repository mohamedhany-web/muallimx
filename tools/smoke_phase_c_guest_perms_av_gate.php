<?php

$model = file_get_contents(__DIR__.'/../app/Models/ClassroomMeeting.php');
$ctrl = file_get_contents(__DIR__.'/../app/Http/Controllers/Student/ClassroomController.php');
$joinCtrl = file_get_contents(__DIR__.'/../app/Http/Controllers/ClassroomJoinController.php');
$room = file_get_contents(__DIR__.'/../resources/views/student/classroom/room.blade.php');
$join = file_get_contents(__DIR__.'/../resources/views/classroom/join.blade.php');

$checks = [
    'model_screen_share' => str_contains($model, 'allowsParticipantScreenShare'),
    'model_chat' => str_contains($model, 'allowsParticipantChat'),
    'model_raise' => str_contains($model, 'allowsParticipantRaiseHand'),
    'model_vbg' => str_contains($model, 'allowsParticipantVirtualBackground'),
    'model_payload' => str_contains($model, 'guestPermissionsPayload'),
    'ctrl_multi_perms' => str_contains($ctrl, 'allow_participant_screen_share'),
    'ctrl_chat_perm' => str_contains($ctrl, 'allow_participant_chat'),
    'join_enter_payload' => str_contains($joinCtrl, 'guestPermissionsPayload()'),
    'room_perms_panel' => str_contains($room, 'mx-guest-perms-panel'),
    'room_perm_whiteboard' => str_contains($room, 'mx-perm-whiteboard'),
    'room_perm_screen' => str_contains($room, 'mx-perm-screen'),
    'room_no_skip_media' => ! str_contains($room, 'btn-join-without-media'),
    'room_forced_av_copy' => str_contains($room, 'فحص الميكروفون والكاميرا إلزامي'),
    'join_av_gate' => str_contains($join, 'guest-av-gate'),
    'join_av_check_btn' => str_contains($join, 'btn-guest-av-check'),
    'join_apply_perms' => str_contains($join, 'applyGuestPermissions'),
    'join_toolbar_builder' => str_contains($join, 'buildGuestToolbarButtons'),
    'join_forced_av_copy' => str_contains($join, 'فحص الصوت والكاميرا إلزامي'),
    'join_no_skip' => ! str_contains($join, 'دخول بدون تفعيل'),
];

$fail = 0;
foreach ($checks as $k => $ok) {
    echo ($ok ? 'OK' : 'FAIL')." {$k}\n";
    if (! $ok) {
        $fail++;
    }
}

exit($fail > 0 ? 1 : 0);
