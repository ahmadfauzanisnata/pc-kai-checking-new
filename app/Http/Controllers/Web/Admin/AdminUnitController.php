<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Unit\StoreUnitRequest;
use App\Http\Requests\Unit\UpdateUnitRequest;
use App\Models\BusinessArea;
use App\Models\Unit;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminUnitController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $query = Unit::with('businessArea')->withCount(['employees', 'users']);

        if ($user->isAdmin()) {
            $query->where('business_area_id', $user->business_area_id);
        } elseif ($request->filled('business_area_id')) {
            $query->where('business_area_id', $request->input('business_area_id'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $units = $query->orderBy('code')->paginate(10)->withQueryString();
        $businessAreas = BusinessArea::where('is_active', true)->get();

        if ($user->isSuperAdmin()) {
            return view('super-admin.units.index', compact('units', 'businessAreas'));
        }

        return view('admin.units.index', compact('units', 'businessAreas'));
    }

    public function store(StoreUnitRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $data = $request->validated();

        if ($user->isAdmin()) {
            $data['business_area_id'] = $user->business_area_id;
        }

        $data['is_active'] = $request->boolean('is_active', true);
        $unit = Unit::create($data);

        ActivityLogService::log(
            'tambah data',
            'Unit Kerja',
            "Membuat Unit Kerja baru {$unit->name} ({$unit->code})",
            $unit
        );

        $redirectRoute = $user->isSuperAdmin() ? 'super-admin.units.index' : 'admin.units.index';
        return redirect()->route($redirectRoute)->with('success', "Unit Kerja {$unit->name} berhasil ditambahkan.");
    }

    public function update(UpdateUnitRequest $request, Unit $unit): RedirectResponse
    {
        $user = Auth::user();

        if ($user->isAdmin() && $unit->business_area_id !== $user->business_area_id) {
            abort(403, 'Anda tidak berwenang mengedit unit kerja di luar Business Area Anda.');
        }

        $data = $request->validated();
        if ($user->isAdmin()) {
            unset($data['business_area_id']);
        }
        $data['is_active'] = $request->boolean('is_active', true);

        $unit->update($data);

        ActivityLogService::log(
            'ubah data',
            'Unit Kerja',
            "Memperbarui Unit Kerja {$unit->name}",
            $unit
        );

        $redirectRoute = $user->isSuperAdmin() ? 'super-admin.units.index' : 'admin.units.index';
        return redirect()->route($redirectRoute)->with('success', "Unit Kerja {$unit->name} berhasil diperbarui.");
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        $user = Auth::user();

        if ($user->isAdmin() && $unit->business_area_id !== $user->business_area_id) {
            abort(403, 'Anda tidak berwenang menghapus unit kerja di luar Business Area Anda.');
        }

        if ($unit->employees()->exists()) {
            return back()->with('error', 'Unit Kerja tidak dapat dihapus karena masih memiliki pegawai terkait.');
        }

        $name = $unit->name;
        $unit->delete();

        ActivityLogService::log(
            'hapus data',
            'Unit Kerja',
            "Menghapus Unit Kerja {$name}",
            $unit
        );

        $redirectRoute = $user->isSuperAdmin() ? 'super-admin.units.index' : 'admin.units.index';
        return redirect()->route($redirectRoute)->with('success', "Unit Kerja {$name} berhasil dihapus.");
    }
}
