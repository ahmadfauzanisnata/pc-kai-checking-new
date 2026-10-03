<?php

namespace App\Http\Controllers\Web\Admin;

use App\Enums\InspectionPeriodStatus;
use App\Enums\InspectionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\InspectionPeriod\StoreInspectionPeriodRequest;
use App\Http\Requests\InspectionPeriod\UpdateInspectionPeriodRequest;
use App\Models\BusinessArea;
use App\Models\InspectionPeriod;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class InspectionPeriodController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $query = InspectionPeriod::with(['businessArea', 'creator'])
            ->withCount([
                'inspections',
                'inspections as completed_count' => function ($q) {
                    $q->whereIn('status', [InspectionStatus::APPROVED, InspectionStatus::LOCKED, InspectionStatus::ARCHIVED]);
                },
                'inspections as verified_count' => function ($q) {
                    $q->whereIn('status', [InspectionStatus::ADMIN_VERIFIED, InspectionStatus::PENDING_SUPER_ADMIN_APPROVAL]);
                },
                'inspections as pending_count' => function ($q) {
                    $q->where('status', InspectionStatus::PENDING_ADMIN_VERIFICATION);
                },
            ]);

        if ($user->isAdmin()) {
            $query->where('business_area_id', $user->business_area_id);
        } elseif ($request->filled('business_area_id')) {
            $query->where('business_area_id', $request->input('business_area_id'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $periods = $query->orderBy('start_date', 'desc')->paginate(10)->withQueryString();
        $businessAreas = BusinessArea::where('is_active', true)->get();

        return view('admin.inspection-periods.index', compact('periods', 'businessAreas'));
    }

    public function store(StoreInspectionPeriodRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $data = $request->validated();

        if ($user->isAdmin()) {
            $data['business_area_id'] = $user->business_area_id;
        }

        $data['status'] = InspectionPeriodStatus::DRAFT;
        $data['created_by'] = $user->id;

        $period = InspectionPeriod::create($data);

        ActivityLogService::log(
            'tambah data',
            'Periode Pemeriksaan',
            "Membuat periode pemeriksaan baru {$period->name}",
            $period
        );

        return redirect()->route('admin.inspection-periods.index')->with('success', "Periode {$period->name} berhasil dibuat.");
    }

    public function update(UpdateInspectionPeriodRequest $request, InspectionPeriod $inspectionPeriod): RedirectResponse
    {
        $user = Auth::user();

        if ($inspectionPeriod->status === InspectionPeriodStatus::LOCKED) {
            return back()->with('error', 'Periode yang telah dikunci tidak dapat diubah.');
        }

        if ($user->isAdmin() && $inspectionPeriod->business_area_id !== $user->business_area_id) {
            abort(403, 'Anda tidak berwenang mengedit periode di luar Business Area Anda.');
        }

        $data = $request->validated();
        if ($user->isAdmin()) {
            unset($data['business_area_id']);
        }

        $inspectionPeriod->update($data);

        ActivityLogService::log(
            'ubah data',
            'Periode Pemeriksaan',
            "Memperbarui periode pemeriksaan {$inspectionPeriod->name}",
            $inspectionPeriod
        );

        return redirect()->route('admin.inspection-periods.index')->with('success', "Periode {$inspectionPeriod->name} berhasil diperbarui.");
    }

    public function activate(InspectionPeriod $inspectionPeriod): RedirectResponse
    {
        $inspectionPeriod->update(['status' => InspectionPeriodStatus::ACTIVE]);

        ActivityLogService::log(
            'ubah data',
            'Periode Pemeriksaan',
            "Mengaktifkan periode pemeriksaan {$inspectionPeriod->name}",
            $inspectionPeriod
        );

        return back()->with('success', "Periode {$inspectionPeriod->name} kini aktif.");
    }

    public function close(InspectionPeriod $inspectionPeriod): RedirectResponse
    {
        $inspectionPeriod->update(['status' => InspectionPeriodStatus::CLOSED]);

        ActivityLogService::log(
            'ubah data',
            'Periode Pemeriksaan',
            "Menutup periode pemeriksaan {$inspectionPeriod->name}",
            $inspectionPeriod
        );

        return back()->with('success', "Periode {$inspectionPeriod->name} telah ditutup.");
    }

    public function lock(InspectionPeriod $inspectionPeriod): RedirectResponse
    {
        $inspectionPeriod->update([
            'status' => InspectionPeriodStatus::LOCKED,
            'locked_at' => now(),
        ]);

        ActivityLogService::log(
            'mengunci',
            'Periode Pemeriksaan',
            "Mengunci periode pemeriksaan {$inspectionPeriod->name}",
            $inspectionPeriod
        );

        return back()->with('success', "Periode {$inspectionPeriod->name} telah dikunci.");
    }

    public function destroy(InspectionPeriod $inspectionPeriod): RedirectResponse
    {
        $user = Auth::user();

        if ($user->isAdmin() && $inspectionPeriod->business_area_id !== $user->business_area_id) {
            abort(403, 'Anda tidak berwenang menghapus periode di luar Business Area Anda.');
        }

        $name = $inspectionPeriod->name;
        $totalInspections = $inspectionPeriod->inspections()->count();

        DB::transaction(function () use ($inspectionPeriod) {
            // Ambil semua pemeriksaan terkait
            $inspections = $inspectionPeriod->inspections()->withTrashed()->get();

            foreach ($inspections as $inspection) {
                // Hapus berkas lampiran dari storage & record di DB
                foreach ($inspection->attachments as $attachment) {
                    if ($attachment->file_path && Storage::disk('public')->exists($attachment->file_path)) {
                        Storage::disk('public')->delete($attachment->file_path);
                    }
                    $attachment->delete();
                }

                // Hapus tanda tangan pegawai dari storage jika ada
                if ($inspection->employee_signature_path && Storage::disk('public')->exists($inspection->employee_signature_path)) {
                    Storage::disk('public')->delete($inspection->employee_signature_path);
                }

                // Hapus data relasi terkait pemeriksaan
                $inspection->checklist()?->delete();
                $inspection->notes()->delete();
                $inspection->approvals()->delete();
                $inspection->statusHistories()->delete();

                // Hapus permanen pemeriksaan (force delete) agar bersih dari query laporan dll.
                $inspection->forceDelete();
            }

            // Hapus data periode pemeriksaan
            $inspectionPeriod->delete();
        });

        ActivityLogService::log(
            'hapus data',
            'Periode Pemeriksaan',
            "Menghapus periode pemeriksaan {$name} beserta {$totalInspections} data pemeriksaan terkait",
            $inspectionPeriod
        );

        return redirect()->route('admin.inspection-periods.index')->with('success', "Periode {$name} beserta seluruh data pemeriksaan di dalamnya berhasil dihapus.");
    }
}
