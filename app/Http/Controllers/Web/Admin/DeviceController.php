<?php

namespace App\Http\Controllers\Web\Admin;

use App\Exports\DeviceExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Device\StoreDeviceRequest;
use App\Http\Requests\Device\UpdateDeviceRequest;
use App\Imports\DeviceImport;
use App\Models\Device;
use App\Models\Employee;
use App\Models\Unit;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DeviceController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $query = Device::with(['employee.unit']);

        if ($user->isAdmin()) {
            $query->whereHas('employee.unit', function ($q) use ($user) {
                $q->where('business_area_id', $user->business_area_id);
            });
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('asset_number', 'like', "%{$search}%")
                    ->orWhere('hostname', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhereHas('employee', function ($eq) use ($search) {
                        $eq->where('name', 'like', "%{$search}%")
                            ->orWhere('nipp', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('device_type')) {
            $query->where('device_type', $request->input('device_type'));
        }

        if ($request->filled('device_status')) {
            $query->where('device_status', $request->input('device_status'));
        }

        if ($request->filled('unit_id')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('unit_id', $request->input('unit_id'));
            });
        }

        $devices = $query->orderBy('asset_number')->paginate(10)->withQueryString();

        $unitsQuery = Unit::where('is_active', true);
        if ($user->isAdmin()) {
            $unitsQuery->where('business_area_id', $user->business_area_id);
        }
        $units = $unitsQuery->get();

        return view('admin.devices.index', compact('devices', 'units'));
    }

    public function create(): View
    {
        $user = Auth::user();
        $employeesQuery = Employee::where('is_active', true);
        if ($user->isAdmin()) {
            $employeesQuery->whereHas('unit', function ($q) use ($user) {
                $q->where('business_area_id', $user->business_area_id);
            });
        }
        $employees = $employeesQuery->orderBy('name')->get();

        return view('admin.devices.create', compact('employees'));
    }

    public function store(StoreDeviceRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $device = Device::create($data);

        ActivityLogService::log(
            'tambah data',
            'Perangkat',
            "Menambahkan perangkat baru {$device->asset_number} ({$device->hostname})",
            $device
        );

        return redirect()->route('admin.devices.index')->with('success', "Perangkat {$device->asset_number} berhasil ditambahkan.");
    }

    public function edit(Device $device): View
    {
        $user = Auth::user();
        if ($user->isAdmin() && $device->employee?->unit?->business_area_id !== $user->business_area_id) {
            abort(403, 'Anda tidak berwenang mengedit perangkat di luar Business Area Anda.');
        }

        $employeesQuery = Employee::where('is_active', true);
        if ($user->isAdmin()) {
            $employeesQuery->whereHas('unit', function ($q) use ($user) {
                $q->where('business_area_id', $user->business_area_id);
            });
        }
        $employees = $employeesQuery->orderBy('name')->get();

        return view('admin.devices.edit', compact('device', 'employees'));
    }

    public function update(UpdateDeviceRequest $request, Device $device): RedirectResponse
    {
        $user = Auth::user();
        if ($user->isAdmin() && $device->employee?->unit?->business_area_id !== $user->business_area_id) {
            abort(403, 'Anda tidak berwenang mengedit perangkat di luar Business Area Anda.');
        }

        $data = $request->validated();
        $device->update($data);

        ActivityLogService::log(
            'ubah data',
            'Perangkat',
            "Memperbarui perangkat {$device->asset_number}",
            $device
        );

        return redirect()->route('admin.devices.index')->with('success', "Perangkat {$device->asset_number} berhasil diperbarui.");
    }

    public function assignEmployee(Request $request, Device $device): RedirectResponse
    {
        $user = Auth::user();
        if ($user->isAdmin() && $device->employee?->unit?->business_area_id !== $user->business_area_id) {
            abort(403, 'Anda tidak berwenang mengubah perangkat di luar Business Area Anda.');
        }

        $request->validate([
            'employee_id' => ['nullable', 'exists:employees,id'],
        ]);

        $device->update([
            'employee_id' => $request->input('employee_id'),
        ]);

        ActivityLogService::log(
            'ubah data',
            'Perangkat',
            "Mengubah pegawai penanggung jawab perangkat {$device->asset_number}",
            $device
        );

        return back()->with('success', "Penanggung jawab perangkat {$device->asset_number} berhasil diperbarui.");
    }

    public function destroy(Device $device): RedirectResponse
    {
        $user = Auth::user();
        if ($user->isAdmin() && $device->employee?->unit?->business_area_id !== $user->business_area_id) {
            abort(403, 'Anda tidak berwenang menghapus perangkat di luar Business Area Anda.');
        }

        if ($device->inspections()->exists()) {
            return back()->with('error', 'Perangkat tidak dapat dihapus karena memiliki riwayat pemeriksaan.');
        }

        $asset = $device->asset_number;
        $device->delete();

        ActivityLogService::log(
            'hapus data',
            'Perangkat',
            "Menghapus perangkat {$asset}",
            $device
        );

        return redirect()->route('admin.devices.index')->with('success', "Perangkat {$asset} berhasil dihapus.");
    }

    public function export(Request $request): BinaryFileResponse
    {
        $unitId = $request->input('unit_id');
        $fileName = 'Data_Perangkat_' . date('Ymd_His') . '.xlsx';

        ActivityLogService::log('export laporan', 'Perangkat', "Export data perangkat ke Excel");

        return Excel::download(new DeviceExport($unitId), $fileName);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
        ]);

        Excel::import(new DeviceImport(), $request->file('file'));

        ActivityLogService::log('tambah data', 'Perangkat', "Import data perangkat dari Excel");

        return redirect()->route('admin.devices.index')->with('success', 'Data perangkat berhasil diimpor.');
    }
}
