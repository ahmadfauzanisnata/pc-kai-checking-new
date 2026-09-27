<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\BusinessArea;
use App\Models\Device;
use App\Models\Employee;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MasterDataApiController extends Controller
{
    public function businessAreas(Request $request): JsonResponse
    {
        $query = BusinessArea::where('is_active', true);
        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(fn($q) => $q->where('name', 'like', "%{$s}%")->orWhere('code', 'like', "%{$s}%"));
        }

        return response()->json([
            'success' => true,
            'data' => $query->get(),
        ]);
    }

    public function units(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Unit::with('businessArea')->where('is_active', true);

        if ($user->isAdmin()) {
            $query->where('business_area_id', $user->business_area_id);
        } elseif ($request->filled('business_area_id')) {
            $query->where('business_area_id', $request->input('business_area_id'));
        }

        return response()->json([
            'success' => true,
            'data' => $query->get(),
        ]);
    }

    public function employees(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Employee::with('unit.businessArea')->where('is_active', true);

        if ($user->isAdmin()) {
            $query->whereHas('unit', fn($q) => $q->where('business_area_id', $user->business_area_id));
        }

        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->input('unit_id'));
        }

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(fn($q) => $q->where('name', 'like', "%{$s}%")->orWhere('nipp', 'like', "%{$s}%"));
        }

        return response()->json([
            'success' => true,
            'data' => $query->paginate(20),
        ]);
    }

    public function devices(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Device::with(['employee.unit']);

        if ($user->isAdmin()) {
            $query->whereHas('employee.unit', fn($q) => $q->where('business_area_id', $user->business_area_id));
        }

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(fn($q) => $q->where('asset_number', 'like', "%{$s}%")->orWhere('hostname', 'like', "%{$s}%"));
        }

        return response()->json([
            'success' => true,
            'data' => $query->paginate(20),
        ]);
    }
}
