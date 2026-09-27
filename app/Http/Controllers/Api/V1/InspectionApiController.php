<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\AttachmentType;
use App\Enums\InspectionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Inspection\SaveChecklistRequest;
use App\Models\Inspection;
use App\Services\AttachmentService;
use App\Services\InspectionService;
use App\Services\InspectionStatusService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InspectionApiController extends Controller
{
    protected InspectionService $inspectionService;
    protected InspectionStatusService $statusService;
    protected AttachmentService $attachmentService;

    public function __construct(
        InspectionService $inspectionService,
        InspectionStatusService $statusService,
        AttachmentService $attachmentService
    ) {
        $this->inspectionService = $inspectionService;
        $this->statusService = $statusService;
        $this->attachmentService = $attachmentService;
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Inspection::with(['employee.unit', 'device', 'inspector', 'inspectionPeriod']);

        if ($user->isInspector()) {
            $query->where('inspector_id', $user->id);
        } elseif ($user->isAdmin()) {
            $query->whereHas('inspectionPeriod', fn($q) => $q->where('business_area_id', $user->business_area_id));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(fn($q) => $q->where('reference_number', 'like', "%{$s}%")
                ->orWhereHas('device', fn($dq) => $dq->where('asset_number', 'like', "%{$s}%")->orWhere('hostname', 'like', "%{$s}%"))
                ->orWhereHas('employee', fn($eq) => $eq->where('name', 'like', "%{$s}%")));
        }

        return response()->json([
            'success' => true,
            'data' => $query->orderBy('created_at', 'desc')->paginate(15),
        ]);
    }

    public function show(Request $request, Inspection $inspection): JsonResponse
    {
        $this->authorizeInspection($request->user(), $inspection);

        $inspection->load([
            'employee.unit.businessArea',
            'device',
            'inspector',
            'inspectionPeriod.businessArea',
            'checklist',
            'attachments.uploadedBy',
            'notes.user',
            'statusHistories.changedBy',
        ]);

        return response()->json([
            'success' => true,
            'data' => $inspection,
        ]);
    }

    public function saveChecklist(SaveChecklistRequest $request, Inspection $inspection): JsonResponse
    {
        $user = $request->user();
        $this->authorizeInspector($user, $inspection);

        try {
            $checklist = $this->inspectionService->saveDraft($inspection, $request->validated(), $user);
            return response()->json([
                'success' => true,
                'message' => 'Checklist berhasil disimpan sebagai draft.',
                'data' => $checklist,
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function submit(Request $request, Inspection $inspection): JsonResponse
    {
        $user = $request->user();
        $this->authorizeInspector($user, $inspection);

        try {
            $this->statusService->submitInspection($inspection, $user, $request->input('comment'));
            return response()->json([
                'success' => true,
                'message' => 'Pemeriksaan berhasil disubmit ke Admin.',
                'data' => $inspection->fresh('status'),
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function uploadAttachment(Request $request, Inspection $inspection): JsonResponse
    {
        $user = $request->user();
        $this->authorizeInspector($user, $inspection);

        $request->validate([
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'attachment_type' => ['required', 'in:DEVICE_PHOTO,SCREENSHOT,SUPPORTING_DOCUMENT,OTHER'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $att = $this->attachmentService->upload(
                $inspection,
                $request->file('file'),
                AttachmentType::from($request->input('attachment_type')),
                $user,
                $request->input('description')
            );

            return response()->json([
                'success' => true,
                'message' => 'Berkas berhasil diunggah.',
                'data' => $att,
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function history(Request $request, Inspection $inspection): JsonResponse
    {
        $this->authorizeInspection($request->user(), $inspection);

        $histories = $inspection->statusHistories()->with('changedBy')->get();
        $notes = $inspection->notes()->with('user')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'status_histories' => $histories,
                'notes' => $notes,
            ],
        ]);
    }

    protected function authorizeInspection($user, Inspection $inspection): void
    {
        if ($user->isInspector() && $inspection->inspector_id !== $user->id) {
            abort(403, 'Akses ditolak.');
        }
        if ($user->isAdmin() && $inspection->inspectionPeriod?->business_area_id !== $user->business_area_id) {
            abort(403, 'Akses ditolak.');
        }
    }

    protected function authorizeInspector($user, Inspection $inspection): void
    {
        if ($user->isInspector() && $inspection->inspector_id !== $user->id) {
            abort(403, 'Akses ditolak.');
        }
    }
}
