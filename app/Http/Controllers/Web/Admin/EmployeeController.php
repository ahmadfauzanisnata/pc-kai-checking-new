<?php

namespace App\Http\Controllers\Web\Admin;

use App\Exports\EmployeeExport;
use App\Exports\EmployeeImportTemplate;
use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\StoreEmployeeRequest;
use App\Http\Requests\Employee\UpdateEmployeeRequest;
use App\Imports\EmployeeImport;
use App\Models\Employee;
use App\Models\Unit;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class EmployeeController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $query = Employee::with(['unit.businessArea'])->withCount('devices');

        if ($user->isAdmin()) {
            $query->whereHas('unit', function ($q) use ($user) {
                $q->where('business_area_id', $user->business_area_id);
            });
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nipp', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('position', 'like', "%{$search}%");
            });
        }

        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->input('unit_id'));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        $employees = $query->orderBy('name')->paginate(10)->withQueryString();

        $unitsQuery = Unit::with('businessArea')->where('is_active', true);
        if ($user->isAdmin()) {
            $unitsQuery->where('business_area_id', $user->business_area_id);
        }
        $units = $unitsQuery->orderBy('business_area_id')->orderBy('name')->get();

        return view('admin.employees.index', compact('employees', 'units'));
    }

    public function create(): View
    {
        $user = Auth::user();
        $unitsQuery = Unit::with('businessArea')->where('is_active', true);
        if ($user->isAdmin()) {
            $unitsQuery->where('business_area_id', $user->business_area_id);
        }
        $units = $unitsQuery->orderBy('business_area_id')->orderBy('name')->get();

        return view('admin.employees.create', compact('units'));
    }

    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);

        $employee = Employee::create($data);

        ActivityLogService::log(
            'tambah data',
            'Pegawai',
            "Menambahkan pegawai baru {$employee->name} (NIPP: {$employee->nipp})",
            $employee
        );

        return redirect()->route('admin.employees.index')->with('success', "Pegawai {$employee->name} berhasil ditambahkan.");
    }

    public function edit(Employee $employee): View
    {
        $user = Auth::user();
        if ($user->isAdmin() && $employee->unit?->business_area_id !== $user->business_area_id) {
            abort(403, 'Anda tidak berwenang mengedit pegawai di luar Business Area Anda.');
        }

        $unitsQuery = Unit::with('businessArea')->where('is_active', true);
        if ($user->isAdmin()) {
            $unitsQuery->where('business_area_id', $user->business_area_id);
        }
        $units = $unitsQuery->orderBy('business_area_id')->orderBy('name')->get();

        return view('admin.employees.edit', compact('employee', 'units'));
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee): RedirectResponse
    {
        $user = Auth::user();
        if ($user->isAdmin() && $employee->unit?->business_area_id !== $user->business_area_id) {
            abort(403, 'Anda tidak berwenang mengedit pegawai di luar Business Area Anda.');
        }

        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);

        $employee->update($data);

        ActivityLogService::log(
            'ubah data',
            'Pegawai',
            "Memperbarui data pegawai {$employee->name}",
            $employee
        );

        return redirect()->route('admin.employees.index')->with('success', "Data pegawai {$employee->name} berhasil diperbarui.");
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $user = Auth::user();
        if ($user->isAdmin() && $employee->unit?->business_area_id !== $user->business_area_id) {
            abort(403, 'Anda tidak berwenang menghapus pegawai di luar Business Area Anda.');
        }

        if ($employee->devices()->exists()) {
            return back()->with('error', 'Pegawai tidak dapat dihapus karena masih memiliki perangkat yang diasosiasikan.');
        }

        $name = $employee->name;
        $employee->delete();

        ActivityLogService::log(
            'hapus data',
            'Pegawai',
            "Menghapus pegawai {$name}",
            $employee
        );

        return redirect()->route('admin.employees.index')->with('success', "Pegawai {$name} berhasil dihapus.");
    }

    public function export(Request $request): BinaryFileResponse
    {
        $unitId = $request->input('unit_id');
        $fileName = 'Data_Pegawai_' . date('Ymd_His') . '.xlsx';

        ActivityLogService::log('export laporan', 'Pegawai', "Export data pegawai ke Excel");

        return Excel::download(new EmployeeExport($unitId), $fileName);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
        ]);

        $user = Auth::user();
        $businessAreaId = $user?->isAdmin() ? $user->business_area_id : null;

        $import = new EmployeeImport($businessAreaId);
        Excel::import($import, $request->file('file'));

        $successCount = $import->getSuccessCount();
        $errors = $import->getErrors();
        $failedCount = count($errors);

        ActivityLogService::log(
            'tambah data',
            'Pegawai',
            "Import data pegawai dari Excel ({$successCount} berhasil, {$failedCount} gagal)"
        );

        if ($successCount > 0 && empty($errors)) {
            return redirect()->route('admin.employees.index')
                ->with('success', "Berhasil mengimpor {$successCount} data pegawai.");
        }

        if ($successCount > 0 && !empty($errors)) {
            return redirect()->route('admin.employees.index')
                ->with('success', "Berhasil mengimpor {$successCount} data pegawai.")
                ->with('warning', "{$failedCount} baris pegawai gagal diimpor karena data tidak valid.")
                ->with('import_errors', $errors);
        }

        if ($successCount === 0 && !empty($errors)) {
            return redirect()->route('admin.employees.index')
                ->with('error', "Gagal mengimpor data pegawai. Semua baris ({$failedCount} baris) gagal validasi.")
                ->with('import_errors', $errors);
        }

        return redirect()->route('admin.employees.index')
            ->with('warning', 'Tidak ada data pegawai yang diproses dari berkas Excel.');
    }

    public function template(): BinaryFileResponse
    {
        return Excel::download(new EmployeeImportTemplate(), 'Template_Import_Pegawai.xlsx');
    }
}
