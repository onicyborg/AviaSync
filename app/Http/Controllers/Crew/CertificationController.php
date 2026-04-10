<?php

namespace App\Http\Controllers\Crew;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CertificationController extends Controller
{
    public function index(): View
    {
        $crew = Auth::user()?->crew;

        $certifications = collect();
        if ($crew) {
            $certifications = $crew->certifications()
                ->orderBy('expiry_date', 'asc')
                ->get();
        }

        return view('crew.certifications.index', [
            'certifications' => $certifications,
        ]);
    }
}
