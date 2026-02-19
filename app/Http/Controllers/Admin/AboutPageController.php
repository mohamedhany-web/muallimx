<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

/**
 * الإدارة العليا - صفحة من نحن
 */
class AboutPageController extends Controller
{
    public function index()
    {
        return view('admin.about.index');
    }

    public function viewPublic()
    {
        return redirect()->route('public.about');
    }
}
