<?php

namespace App\Http\Controllers;

use App\Mail\DemoRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class DemoRequestController extends Controller
{
    /**
     * Where demo requests are delivered. Central inbox for the product team.
     */
    private const RECIPIENT = 'admin@bangicode.ma';

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:120',
            'company' => 'required|string|max:160',
            'email'   => 'required|email|max:160',
            'phone'   => 'nullable|string|max:40',
            'message' => 'nullable|string|max:2000',
        ]);

        Mail::to(self::RECIPIENT)->send(new DemoRequest($data));

        return back()->with('success', __('Thanks! Your demo request has been sent — we\'ll be in touch shortly.'));
    }
}
