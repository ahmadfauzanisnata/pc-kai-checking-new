<?php

namespace App\Http\Controllers\Web\Inspector;

use App\Enums\AttachmentType;
use App\Enums\InspectionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Inspection\ResubmitInspectionRequest;
use App\Http\Requests\Inspection\SaveChecklistRequest;
use App\Http\Requests\Inspection\SubmitInspectionRequest;
use App\Models\Inspection;
use App\Models\InspectionAttachment;
use App\Services\AttachmentService;
use App\Services\InspectionService;
use App\Services\InspectionStatusService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InspectorTaskController extends Controller
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

    public function dashboard(): View
    {
        $user = Auth::user();

        $stats = [
            'new_tasks' => Inspection::where('inspector_id', $user->id)->where('status', InspectionStatus::ASSIGNED)->count(),
            'in_progress' => Inspection::where('inspector_id', $user->id)->where('status', InspectionStatus::IN_PROGRESS)->count(),
            'revision_required' => Inspection::where('inspector_id', $user->id)->where('status', InspectionStatus::REVISION_REQUIRED)->count(),
            'submitted' => Inspection::where('inspector_id', $user->id)->whereIn('status', [
                InspectionStatus::PENDING_ADMIN_VERIFICATION,
                InspectionStatus::RESUBMITTED,
                InspectionStatus::ADMIN_VERIFIED,
                InspectionStatus::PENDING_SUPER_ADMIN_APPROVAL
            ])->count(),
            'completed' => Inspection::where('inspector_id', $user->id)->whereIn('status', [
                InspectionStatus::APPROVED,
                InspectionStatus::LOCKED,
                InspectionStatus::ARCHIVED
            ])->count(),
        ];

        $recentTasks = Inspection::with(['employee.unit', 'device', 'inspectionPeriod'])
            ->where('inspector_id', $user->id)
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();

        return view('inspector.dashboard', compact('stats', 'recentTasks'));
    }

    public function index(Request $request): View
    {
        $user = Auth::user();
        $query = Inspection::with(['employee.unit', 'device', 'inspectionPeriod', 'checklist'])
            ->where('inspector_id', $user->id);

        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'submitted') {
                $query->whereIn('status', [
                    InspectionStatus::PENDING_ADMIN_VERIFICATION,
                    InspectionStatus::RESUBMITTED,
                    InspectionStatus::ADMIN_VERIFIED,
                    InspectionStatus::PENDING_SUPER_ADMIN_APPROVAL
                ]);
            } else {
                $query->where('status', $status);
            }
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('reference_number', 'like', "%{$search}%")
                    ->orWhereHas('device', function ($dq) use ($search) {
                        $dq->where('asset_number', 'like', "%{$search}%")
                            ->orWhere('hostname', 'like', "%{$search}%");
                    })
                    ->orWhereHas('employee', function ($eq) use ($search) {
                        $eq->where('name', 'like', "%{$search}%")
                            ->orWhere('nipp', 'like', "%{$search}%");
                    });
            });
        }

        $tasks = $query->orderBy('document_date', 'desc')->paginate(10)->withQueryString();

        return view('inspector.tasks.index', compact('tasks'));
    }

    public function show(Inspection $inspection): View
    {
        $this->authorizeInspector($inspection);
        $inspection->load(['employee.unit.businessArea', 'device', 'inspectionPeriod', 'checklist', 'notes.user', 'attachments', 'statusHistories.changedBy']);

        return view('inspector.tasks.show', compact('inspection'));
    }

    public function start(Inspection $inspection): RedirectResponse
    {
        $this->authorizeInspector($inspection);

        try {
            $this->statusService->startInspection($inspection, Auth::user());
            return redirect()->route('inspector.tasks.checklist', $inspection)
                ->with('success', 'Pemeriksaan dimulai. Silakan isi checklist pemeriksaan.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function checklist(Inspection $inspection): View|RedirectResponse
    {
        $this->authorizeInspector($inspection);

        // If still assigned, prompt to start first
        if ($inspection->status === InspectionStatus::ASSIGNED) {
            return redirect()->route('inspector.tasks.show', $inspection)
                ->with('info', 'Silakan klik tombol "Mulai Pemeriksaan" terlebih dahulu.');
        }

        // If returned for revision, redirect to revision view
        if ($inspection->status === InspectionStatus::REVISION_REQUIRED) {
            return redirect()->route('inspector.tasks.revision', $inspection);
        }

        $inspection->load(['employee.unit.businessArea', 'device', 'inspectionPeriod', 'checklist', 'attachments']);

        return view('inspector.tasks.checklist', compact('inspection'));
    }

    public function saveDraft(SaveChecklistRequest $request, Inspection $inspection): RedirectResponse
    {
        $this->authorizeInspector($inspection);

        try {
            $this->inspectionService->saveDraft($inspection, $request->validated(), Auth::user());
            return back()->with('success', 'Draft checklist berhasil disimpan.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function submit(SubmitInspectionRequest $request, Inspection $inspection): RedirectResponse
    {
        $this->authorizeInspector($inspection);

        try {
            // First save checklist data
            $this->inspectionService->saveDraft($inspection, $request->validated(), Auth::user());

            // Then submit
            $this->statusService->submitInspection($inspection, Auth::user(), $request->input('comment'));

            return redirect()->route('inspector.tasks.show', $inspection)
                ->with('success', 'Hasil pemeriksaan berhasil dikirim ke Admin untuk verifikasi.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function revision(Inspection $inspection): View
    {
        $this->authorizeInspector($inspection);
        $inspection->load(['employee.unit', 'device', 'inspectionPeriod', 'checklist', 'notes.user', 'attachments', 'statusHistories.changedBy']);

        $lastRevisionNote = $inspection->notes->where('note_type', \App\Enums\NoteType::ADMIN_REVISION)->first();
        $lastHistory = $inspection->statusHistories->where('action', 'request_revision')->first();

        return view('inspector.tasks.revision', compact('inspection', 'lastRevisionNote', 'lastHistory'));
    }

    public function resubmit(ResubmitInspectionRequest $request, Inspection $inspection): RedirectResponse
    {
        $this->authorizeInspector($inspection);

        try {
            // Update checklist items
            $this->inspectionService->saveDraft($inspection, $request->validated(), Auth::user());

            // Resubmit with notes
            $this->statusService->resubmitInspection($inspection, Auth::user(), $request->input('notes'));

            return redirect()->route('inspector.tasks.show', $inspection)
                ->with('success', 'Perbaikan pemeriksaan berhasil dikirim ulang kepada Admin.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function uploadAttachment(Request $request, Inspection $inspection): RedirectResponse
    {
        $this->authorizeInspector($inspection);

        $request->validate([
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'attachment_type' => ['required', 'in:DEVICE_PHOTO,SCREENSHOT,SUPPORTING_DOCUMENT,OTHER'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $this->attachmentService->upload(
                $inspection,
                $request->file('file'),
                AttachmentType::from($request->input('attachment_type')),
                Auth::user(),
                $request->input('description')
            );

            return back()->with('success', 'Berkas bukti berhasil diunggah.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function deleteAttachment(InspectionAttachment $attachment): RedirectResponse
    {
        $inspection = $attachment->inspection;
        $this->authorizeInspector($inspection);

        $this->attachmentService->delete($attachment, Auth::user());

        return back()->with('success', 'Berkas lampiran berhasil dihapus.');
    }

    public function history(): View
    {
        $user = Auth::user();

        $history = Inspection::with(['employee.unit', 'device', 'inspectionPeriod', 'checklist'])
            ->where('inspector_id', $user->id)
            ->whereIn('status', [InspectionStatus::APPROVED, InspectionStatus::LOCKED, InspectionStatus::ARCHIVED])
            ->orderBy('completed_at', 'desc')
            ->paginate(10);

        return view('inspector.history', compact('history'));
    }

    protected function authorizeInspector(Inspection $inspection): void
    {
        $user = Auth::user();
        if ($user->isInspector() && $inspection->inspector_id !== $user->id) {
            abort(403, 'Anda tidak berwenang mengakses tugas pemeriksaan milik pemeriksa lain.');
        }
    }
}
