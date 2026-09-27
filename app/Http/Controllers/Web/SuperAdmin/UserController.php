<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\BusinessArea;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::with(['role', 'businessArea', 'unit']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('nipp', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role_id')) {
            $query->where('role_id', $request->input('role_id'));
        }

        if ($request->filled('business_area_id')) {
            $query->where('business_area_id', $request->input('business_area_id'));
        }

        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->input('unit_id'));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        $users = $query->orderBy('name')->paginate(10)->withQueryString();

        $roles = Role::all();
        $businessAreas = BusinessArea::where('is_active', true)->get();
        $units = Unit::where('is_active', true)->get();

        return view('super-admin.users.index', compact('users', 'roles', 'businessAreas', 'units'));
    }

    public function create(): View
    {
        $roles = Role::all();
        $businessAreas = BusinessArea::where('is_active', true)->get();
        $units = Unit::where('is_active', true)->get();

        return view('super-admin.users.create', compact('roles', 'businessAreas', 'units'));
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $data['is_active'] = $request->boolean('is_active', true);

        $user = User::create($data);

        ActivityLogService::log(
            'tambah data',
            'Manajemen Pengguna',
            "Membuat pengguna baru {$user->name} ({$user->role?->display_name})",
            $user,
            null,
            $data
        );

        return redirect()->route('super-admin.users.index')->with('success', "Pengguna {$user->name} berhasil ditambahkan.");
    }

    public function edit(User $user): View
    {
        $roles = Role::all();
        $businessAreas = BusinessArea::where('is_active', true)->get();
        $units = Unit::where('is_active', true)->get();

        return view('super-admin.users.edit', compact('user', 'roles', 'businessAreas', 'units'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        $oldValues = $user->only(['name', 'email', 'nipp', 'role_id', 'business_area_id', 'unit_id', 'is_active']);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $data['is_active'] = $request->boolean('is_active', true);
        $user->update($data);

        ActivityLogService::log(
            'ubah data',
            'Manajemen Pengguna',
            "Memperbarui data pengguna {$user->name}",
            $user,
            $oldValues,
            $data
        );

        return redirect()->route('super-admin.users.index')->with('success', "Data pengguna {$user->name} berhasil diperbarui.");
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $statusText = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        ActivityLogService::log(
            'ubah data',
            'Manajemen Pengguna',
            "Akun pengguna {$user->name} telah {$statusText}.",
            $user
        );

        return back()->with('success', "Akun {$user->name} berhasil {$statusText}.");
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $newPassword = $request->input('new_password', 'password');
        $user->password = Hash::make($newPassword);
        $user->save();

        ActivityLogService::log(
            'ubah data',
            'Manajemen Pengguna',
            "Reset password pengguna {$user->name}",
            $user
        );

        return back()->with('success', "Kata sandi untuk {$user->name} berhasil direset menjadi: {$newPassword}");
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $name = $user->name;
        $user->delete();

        ActivityLogService::log(
            'hapus data',
            'Manajemen Pengguna',
            "Menghapus pengguna {$name}",
            $user
        );

        return redirect()->route('super-admin.users.index')->with('success', "Pengguna {$name} berhasil dihapus.");
    }
}
