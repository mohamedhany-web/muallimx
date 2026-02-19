<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;

/**
 * مجتمع البيانات والذكاء الاصطناعي
 * (مسابقات، مجموعات بيانات، مناقشات - مستوحى من Kaggle)
 */
class CommunityController extends Controller
{
    /**
     * صفحة المجتمع الرئيسية
     */
    public function index()
    {
        return view('public.community.index');
    }
}
