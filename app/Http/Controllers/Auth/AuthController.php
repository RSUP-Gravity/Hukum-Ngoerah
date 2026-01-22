<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        // Check rate limiting
        $throttleKey = $this->throttleKey($request);
        
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            
            AuditLog::log(
                'login_blocked',
                AuditLog::MODULE_AUTH,
                null,
                null,
                $request->username,
                null,
                null,
                "Login diblokir karena terlalu banyak percobaan. Coba lagi dalam {$seconds} detik."
            );

            throw ValidationException::withMessages([
                'username' => [
                    "Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik."
                ],
            ]);
        }

        // Attempt login with username
        $credentials = [
            'username' => $request->username,
            'password' => $request->password,
        ];

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey, 60 * 15); // Lock for 15 minutes

            AuditLog::log(
                'login_failed',
                AuditLog::MODULE_AUTH,
                null,
                null,
                $request->username,
                null,
                null,
                'Login gagal: username atau password salah.'
            );

            throw ValidationException::withMessages([
                'username' => ['Username atau password salah.'],
            ]);
        }

        // Check if user is active
        $user = Auth::user();
        
        if (!$user->is_active) {
            Auth::logout();
            
            throw ValidationException::withMessages([
                'username' => ['Akun Anda tidak aktif. Hubungi administrator.'],
            ]);
        }

        // Clear rate limiter
        RateLimiter::clear($throttleKey);

        // Regenerate session
        $request->session()->regenerate();

        // Record login
        $user->recordLogin();

        // Log successful login
        AuditLog::log(
            'login_success',
            AuditLog::MODULE_AUTH,
            'User',
            $user->id,
            $user->username,
            null,
            null,
            'Login berhasil.'
        );

        // Check if password change is required
        if ($user->needsPasswordChange()) {
            return redirect()->route('password.change')
                ->with('warning', 'Anda harus mengganti password Anda.');
        }

        // Set session flag for login notification popup
        $request->session()->put('show_login_notification', true);

        return redirect()->intended(route('dashboard'))
            ->with('success', "Selamat datang, {$user->name}!");
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            AuditLog::log(
                'logout',
                AuditLog::MODULE_AUTH,
                'User',
                $user->id,
                $user->username,
                null,
                null,
                'Logout berhasil.'
            );
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Anda telah berhasil logout.');
    }

    /**
     * Get throttle key for rate limiting
     */
    protected function throttleKey(Request $request): string
    {
        return strtolower($request->input('username')) . '|' . $request->ip();
    }
}
