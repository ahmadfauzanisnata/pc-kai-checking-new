<?php

namespace App\Http\Controllers\Web\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request): RedirectResponse
    {
        $loginInput = $request->input('login') ?? $request->input('email');

        if (empty($loginInput)) {
            throw ValidationException::withMessages([
                'login' => 'Email atau NIPP wajib diisi.',
                'email' => 'Email atau NIPP wajib diisi.',
            ]);
        }

        $request->validate([
            'password' => ['required', 'string'],
        ], [
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $password = $request->input('password');
        $remember = $request->boolean('remember');

        // Allow login by email or NIPP
        $user = User::where('email', $loginInput)
            ->orWhere('nipp', $loginInput)
            ->first();

        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'login' => 'Email/NIPP atau kata sandi yang Anda masukkan salah.',
                'email' => 'Email/NIPP atau kata sandi yang Anda masukkan salah.',
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'login' => 'Akun Anda dinonaktifkan. Silakan hubungi Administrator.',
            ]);
        }

        Auth::login($user, $remember);
        $request->session()->regenerate();

        $user->update(['last_login_at' => now()]);

        ActivityLogService::log(
            'login',
            'Autentikasi',
            "Pengguna {$user->name} ({$user->role?->display_name}) berhasil masuk ke dalam sistem.",
            $user,
            null,
            null,
            $user->id
        );

        return $this->redirectBasedOnRole($user)
            ->with('success', "Selamat datang kembali, {$user->name}!");
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user) {
            ActivityLogService::log(
                'logout',
                'Autentikasi',
                "Pengguna {$user->name} keluar dari sistem.",
                $user,
                null,
                null,
                $user->id
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'Anda telah berhasil keluar dari sistem.');
    }

    /**
     * Redirect user based on role
     */
    protected function redirectBasedOnRole(User $user): RedirectResponse
    {
        $roleName = $user->role?->name;

        return match ($roleName) {
            UserRole::SUPER_ADMIN->value => redirect()->intended(route('super-admin.dashboard')),
            UserRole::ADMIN->value => redirect()->intended(route('admin.dashboard')),
            UserRole::INSPECTOR->value => redirect()->intended(route('inspector.dashboard')),
            default => redirect()->route('login'),
        };
    }
}
