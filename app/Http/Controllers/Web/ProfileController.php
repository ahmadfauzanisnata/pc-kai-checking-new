<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(): View
    {
        $user = Auth::user()->load(['role', 'businessArea', 'unit']);
        return view('profile.show', compact('user'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'signature' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
        ]);

        $user->name = $request->input('name');
        $user->phone = $request->input('phone');

        if ($request->hasFile('signature')) {
            // Delete old signature if exists
            if ($user->signature_path && Storage::disk('public')->exists($user->signature_path)) {
                Storage::disk('public')->delete($user->signature_path);
            }

            $sigName = 'signatures/' . Str::random(40) . '.' . $request->file('signature')->getClientOriginalExtension();
            $path = $request->file('signature')->storeAs('signatures', basename($sigName), 'public');
            $user->signature_path = $path;
        }

        $user->save();

        ActivityLogService::log(
            'ubah profil',
            'Pengguna',
            "Memperbarui profil pengguna {$user->name}",
            $user,
            null,
            null,
            $user->id
        );

        return back()->with('success', 'Profil Anda berhasil diperbarui.');
    }

    public function editPassword(): View
    {
        return view('profile.password');
    }

    public function showPasswordForm(): View
    {
        return $this->editPassword();
    }

    public function updateSignature(Request $request): RedirectResponse
    {
        return $this->update($request);
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ], [
            'current_password.current_password' => 'Kata sandi saat ini tidak cocok.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        $user->update([
            'password' => Hash::make($request->input('password')),
        ]);

        ActivityLogService::log(
            'ubah password',
            'Pengguna',
            "Mengubah kata sandi pengguna {$user->name}",
            $user,
            null,
            null,
            $user->id
        );

        return back()->with('success', 'Kata sandi Anda berhasil diubah.');
    }
}
