<?php

namespace App\Http\Controllers;

use App\Models\ClassroomMeeting;
use App\Models\LiveSetting;
use Illuminate\Http\Request;

class ClassroomJoinController extends Controller
{
    /**
     * صفحة الدخول كضيف — لا تتطلب تسجيل دخول.
     * الرابط يُشارك من المعلم: /classroom/join/{code}
     */
    public function show(string $code)
    {
        $code = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $code));
        if (strlen($code) < 4) {
            abort(404, 'كود الغرفة غير صالح.');
        }

        $roomName = 'MuallimX-' . $code;
        $meeting = ClassroomMeeting::where('code', $code)->first();
        $jitsiDomain = LiveSetting::getJitsiDomain();
        $joinUrl = url('classroom/join/' . $code);

        return view('classroom.join', compact('code', 'roomName', 'meeting', 'jitsiDomain', 'joinUrl'));
    }
}
