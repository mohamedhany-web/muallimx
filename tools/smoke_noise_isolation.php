<?php

$js = file_get_contents(__DIR__.'/../public/js/classroom-noise-isolation.js');
$room = file_get_contents(__DIR__.'/../resources/views/student/classroom/room.blade.php');
$join = file_get_contents(__DIR__.'/../resources/views/classroom/join.blade.php');

$checks = [
    'js_module' => str_contains($js, 'MxClassroomNoiseIsolation'),
    'js_setNoise' => str_contains($js, 'setNoiseSuppressionEnabled'),
    'js_constraints' => str_contains($js, 'noiseSuppression: true'),
    'js_enhance' => str_contains($js, 'enhanceMicStreamForRecording'),
    'js_patch' => str_contains($js, 'getJitsiAudioConfigPatch'),
    'room_inline' => str_contains($room, 'mx-classroom-noise-js'),
    'room_btn' => str_contains($room, 'mx-ml-btn-noise'),
    'room_bind' => str_contains($room, 'bindNoiseIsolationUi'),
    'room_enable_on_join' => substr_count($room, '__mxNoiseUi.enableOnJoin'),
    'room_audio_patch' => str_contains($room, 'getJitsiAudioConfigPatch'),
    'room_mic_clean' => str_contains($room, 'getCleanMicAudioConstraints'),
    'room_mic_hp' => str_contains($room, "micHp.type = 'highpass'"),
    'join_inline' => str_contains($join, 'mx-classroom-noise-js'),
    'join_btn' => str_contains($join, 'mx-ml-btn-noise'),
    'join_bind' => str_contains($join, 'bindGuestNoiseIsolation'),
    'join_audio_patch' => str_contains($join, 'getJitsiAudioConfigPatch'),
];

$fail = 0;
foreach ($checks as $k => $ok) {
    echo ($ok ? 'OK' : 'FAIL')." {$k}\n";
    if (! $ok) {
        $fail++;
    }
}

exit($fail > 0 ? 1 : 0);
