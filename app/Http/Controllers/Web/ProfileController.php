<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
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
            'signature_data' => ['nullable', 'string'],
        ]);

        $user->name = $request->input('name');
        $user->phone = $request->input('phone');

        // Handle signature from canvas drawing (base64)
        if ($request->filled('signature_data')) {
            $sigData = $request->input('signature_data');
            if ($sigData === 'clear' || $sigData === 'delete') {
                if ($user->signature_path && Storage::disk('public')->exists($user->signature_path)) {
                    Storage::disk('public')->delete($user->signature_path);
                }
                $user->signature_path = null;
            } elseif (preg_match('/^data:image\/(\w+);base64,/', $sigData)) {
                $data = substr($sigData, strpos($sigData, ',') + 1);
                $decoded = base64_decode($data);
                if ($decoded !== false) {
                    if ($user->signature_path && Storage::disk('public')->exists($user->signature_path)) {
                        Storage::disk('public')->delete($user->signature_path);
                    }
                    $filename = 'signatures/' . Str::random(40) . '.png';
                    Storage::disk('public')->put($filename, $decoded);
                    $user->signature_path = $filename;
                }
            }
        } elseif ($request->hasFile('signature')) {
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

    public function updateSignature(Request $request): JsonResponse|RedirectResponse
    {
        $user = Auth::user();

        $request->validate([
            'signature_data' => ['required', 'string'],
        ]);

        $signatureData = $request->input('signature_data');

        if ($signatureData === 'clear' || $signatureData === 'delete') {
            if ($user->signature_path && Storage::disk('public')->exists($user->signature_path)) {
                Storage::disk('public')->delete($user->signature_path);
            }
            $user->signature_path = null;
            $user->save();

            ActivityLogService::log(
                'hapus tanda tangan',
                'Pengguna',
                "Menghapus spesimen tanda tangan pengguna {$user->name}",
                $user,
                null,
                null,
                $user->id
            );

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Spesimen tanda tangan berhasil dihapus.',
                    'signature_url' => null,
                ]);
            }

            return back()->with('success', 'Spesimen tanda tangan berhasil dihapus.');
        }

        if (preg_match('/^data:image\/(\w+);base64,/', $signatureData)) {
            $data = substr($signatureData, strpos($signatureData, ',') + 1);
            $decoded = base64_decode($data);

            if ($decoded === false) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Format gambar tanda tangan tidak valid.',
                    ], 422);
                }
                return back()->with('error', 'Format gambar tanda tangan tidak valid.');
            }

            if ($user->signature_path && Storage::disk('public')->exists($user->signature_path)) {
                Storage::disk('public')->delete($user->signature_path);
            }

            $filename = 'signatures/' . Str::random(40) . '.png';
            Storage::disk('public')->put($filename, $decoded);
            $user->signature_path = $filename;
            $user->save();

            ActivityLogService::log(
                'ubah tanda tangan',
                'Pengguna',
                "Memperbarui spesimen tanda tangan digital pengguna {$user->name}",
                $user,
                null,
                null,
                $user->id
            );

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Spesimen tanda tangan digital berhasil disimpan.',
                    'signature_url' => Storage::url($filename),
                ]);
            }

            return back()->with('success', 'Spesimen tanda tangan digital berhasil disimpan.');
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Format data tanda tangan tidak valid.',
            ], 422);
        }

        return back()->with('error', 'Format data tanda tangan tidak valid.');
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
