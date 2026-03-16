<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MySubscriptionController extends Controller
{
    /**
     * عرض صفحة اشتراك الطالب الحالي (الباقة، المدة، تاريخ الانتهاء، المزايا).
     */
    public function show(Request $request)
    {
        $user = Auth::user();
        $subscription = $user->activeSubscription();

        if (!$subscription) {
            return redirect()->route('public.pricing')
                ->with('info', 'ليس لديك اشتراك نشط. يمكنك الاشتراك في إحدى الباقات من صفحة التسعير.');
        }

        return view('student.my-subscription', [
            'subscription' => $subscription,
        ]);
    }
}
