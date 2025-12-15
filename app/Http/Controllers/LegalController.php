<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class LegalController extends Controller
{
    /**
     * Display the Privacy Policy page.
     */
    public function privacy(): View
    {
        return view('legal.privacy');
    }

    /**
     * Display the Terms & Conditions page.
     */
    public function terms(): View
    {
        return view('legal.terms');
    }

    /**
     * Display the Pricing page.
     */
    public function pricing(): View
    {
        return view('legal.pricing', [
            'pricing' => config('payments.pricing'),
            'duration_days' => config('payments.duration_days'),
        ]);
    }

    /**
     * Display the About page.
     */
    public function about(): View
    {
        return view('legal.about');
    }
}
