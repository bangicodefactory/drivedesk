<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\LoggedHistory;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $user=\App\Models\User::find(1);
        if ($user) { \App::setLocale($user->lang); }
        return Inertia::render('Auth/Login', [
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param  \App\Http\Requests\Auth\LoginRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(LoginRequest $request)
    {

        // BAN-200: captcha temporarily disabled on the Inertia path — the React
        // Login form does not yet render a reCAPTCHA widget. Tracked in BAN-204.
        // The Blade view still posts g-recaptcha-response, but we no longer
        // gate on it here because the React form has no field to fill it.
        // Operators must keep google_recaptcha=off until BAN-204 ships.
        $validation = [];
        $this->validate($request, $validation);

        $request->authenticate();
        $request->session()->regenerate();
        $loginUser = Auth::user();
        if($loginUser->is_active == 0)
        {
            auth()->logout();
        }
        if (empty($loginUser->email_verified_at)) {
            auth()->logout();
            return redirect()->route('login')->with('error', __('Please verify you account first.'));
        }

        userLoggedHistory();
        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
