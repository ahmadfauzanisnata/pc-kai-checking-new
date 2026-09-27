<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BusinessArea\StoreBusinessAreaRequest;
use App\Http\Requests\BusinessArea\UpdateBusinessAreaRequest;
use App\Models\BusinessArea;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BusinessAreaController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $query = BusinessArea::withCount(['units', 'users', 'inspectionPeriods']);

        if ($user->isAdmin()) {
            $query->where('id', $user->business_area_id);
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $businessAreas = $query->orderBy('code')->paginate(10)->withQueryString();

        if ($user->isAdmin()) {
            return view('admin.business-areas.index', compact('businessAreas'));
        }

        return view('super-admin.business-areas.index', compact('businessAreas'));
    }

    public function store(StoreBusinessAreaRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);

        $ba = BusinessArea::create($data);

        ActivityLogService::log(
            'tambah data',
            'Business Area',
            "Membuat Business Area baru {$ba->name} ({$ba->code})",
            $ba
        );

        return redirect()->route('super-admin.business-areas.index')->with('success', "Business Area {$ba->name} berhasil ditambahkan.");
    }

    public function update(UpdateBusinessAreaRequest $request, BusinessArea $businessArea): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);

        $businessArea->update($data);

        ActivityLogService::log(
            'ubah data',
            'Business Area',
            "Memperbarui Business Area {$businessArea->name}",
            $businessArea
        );

        return redirect()->route('super-admin.business-areas.index')->with('success', "Business Area {$businessArea->name} berhasil diperbarui.");
    }

    public function destroy(BusinessArea $businessArea): RedirectResponse
    {
        if ($businessArea->units()->exists()) {
            return back()->with('error', 'Business Area tidak dapat dihapus karena masih memiliki unit kerja.');
        }

        $name = $businessArea->name;
        $businessArea->delete();

        ActivityLogService::log(
            'hapus data',
            'Business Area',
            "Menghapus Business Area {$name}",
            $businessArea
        );

        return redirect()->route('super-admin.business-areas.index')->with('success', "Business Area {$name} berhasil dihapus.");
    }
}
