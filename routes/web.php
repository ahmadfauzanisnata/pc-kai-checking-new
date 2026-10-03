<?php

use App\Http\Controllers\Web\Admin\AdminUnitController;
use App\Http\Controllers\Web\Admin\AdminVerificationController;
use App\Http\Controllers\Web\Admin\AssignmentController;
use App\Http\Controllers\Web\Admin\DeviceController;
use App\Http\Controllers\Web\Admin\EmployeeController;
use App\Http\Controllers\Web\Admin\InspectionPeriodController;
use App\Http\Controllers\Web\Auth\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\Inspector\InspectorTaskController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\ReportController;
use App\Http\Controllers\Web\SuperAdmin\ActivityLogController;
use App\Http\Controllers\Web\SuperAdmin\BusinessAreaController;
use App\Http\Controllers\Web\SuperAdmin\FormConfigurationController;
use App\Http\Controllers\Web\SuperAdmin\SuperAdminInspectionController;
use App\Http\Controllers\Web\SuperAdmin\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Profile Management
    Route::prefix('profile')->as('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'show'])->name('show');
        Route::put('/', [ProfileController::class, 'update'])->name('update');
        Route::get('/password', [ProfileController::class, 'editPassword'])->name('password');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.update');
        Route::post('/signature', [ProfileController::class, 'updateSignature'])->name('signature');
    });

    // Reports & Exports
    Route::prefix('reports')->as('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/excel', [ReportController::class, 'exportExcel'])->name('excel');
        Route::get('/period/{period}/excel', [ReportController::class, 'exportPeriod'])->name('period.excel');
        Route::get('/period/{period}/pdf', [ReportController::class, 'downloadPeriodPdf'])->name('period.pdf');
        Route::get('/period/{period}/zip', [ReportController::class, 'downloadPeriodZip'])->name('period.zip');
        Route::get('/recap-pdf', [ReportController::class, 'downloadRecapPdf'])->name('recap-pdf');
        Route::get('/zip', [ReportController::class, 'downloadZip'])->name('zip');
        Route::get('/{inspection}/preview', [ReportController::class, 'preview'])->name('preview');
        Route::get('/{inspection}/pdf', [ReportController::class, 'downloadPdf'])->name('pdf');
    });

    // Super Admin Routes
    Route::middleware('role:super_admin')->prefix('super-admin')->as('super-admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'superAdmin'])->name('dashboard');

        Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::match(['patch', 'post'], '/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::resource('users', UserController::class);
        Route::resource('business-areas', BusinessAreaController::class);
        Route::resource('units', AdminUnitController::class);

        Route::get('/form-configuration', [FormConfigurationController::class, 'index'])->name('form-configuration.index');
        Route::put('/form-configuration', [FormConfigurationController::class, 'update'])->name('form-configuration.update');

        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');

        // Inspection Management & Approval
        Route::prefix('inspections')->as('inspections.')->group(function () {
            Route::get('/', [SuperAdminInspectionController::class, 'index'])->name('index');
            Route::get('/pending', [SuperAdminInspectionController::class, 'pending'])->name('pending');
            Route::get('/approved', [SuperAdminInspectionController::class, 'approved'])->name('approved');
            Route::get('/locked', [SuperAdminInspectionController::class, 'locked'])->name('locked');
            Route::get('/archived', [SuperAdminInspectionController::class, 'archived'])->name('archived');

            Route::get('/{inspection}/approval', [SuperAdminInspectionController::class, 'showApproval'])->name('show-approval');
            Route::match(['post', 'patch'], '/{inspection}/approval', [SuperAdminInspectionController::class, 'handleApproval'])->name('handle-approval');
            Route::match(['post', 'patch'], '/{inspection}/lock', [SuperAdminInspectionController::class, 'lock'])->name('lock');
            Route::match(['post', 'patch'], '/{inspection}/unlock', [SuperAdminInspectionController::class, 'unlock'])->name('unlock');
            Route::match(['post', 'patch'], '/{inspection}/archive', [SuperAdminInspectionController::class, 'archive'])->name('archive');
        });
    });

    // Admin Routes
    Route::middleware('role:admin,super_admin')->prefix('admin')->as('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');

        Route::get('/business-areas', [BusinessAreaController::class, 'index'])->name('business-areas.index');
        Route::resource('units', AdminUnitController::class);

        // Employees
        Route::get('/employees/export', [EmployeeController::class, 'export'])->name('employees.export');
        Route::get('/employees/template', [EmployeeController::class, 'template'])->name('employees.template');
        Route::post('/employees/import', [EmployeeController::class, 'import'])->name('employees.import');
        Route::resource('employees', EmployeeController::class);

        // Devices
        Route::get('/devices/export', [DeviceController::class, 'export'])->name('devices.export');
        Route::get('/devices/template', [DeviceController::class, 'template'])->name('devices.template');
        Route::post('/devices/import', [DeviceController::class, 'import'])->name('devices.import');
        Route::patch('/devices/{device}/assign-employee', [DeviceController::class, 'assignEmployee'])->name('devices.assign-employee');
        Route::resource('devices', DeviceController::class);

        // Inspection Periods
        Route::match(['post', 'patch'], '/inspection-periods/{inspectionPeriod}/activate', [InspectionPeriodController::class, 'activate'])->name('inspection-periods.activate');
        Route::match(['post', 'patch'], '/inspection-periods/{inspectionPeriod}/close', [InspectionPeriodController::class, 'close'])->name('inspection-periods.close');
        Route::match(['post', 'patch'], '/inspection-periods/{inspectionPeriod}/lock', [InspectionPeriodController::class, 'lock'])->name('inspection-periods.lock');
        Route::resource('inspection-periods', InspectionPeriodController::class);

        // Batch Assignments
        Route::get('/assignments/create', [AssignmentController::class, 'create'])->name('assignments.create');
        Route::post('/assignments', [AssignmentController::class, 'store'])->name('assignments.store');

        // Verifications
        Route::prefix('verifications')->as('verifications.')->group(function () {
            Route::get('/', [AdminVerificationController::class, 'index'])->name('index');
            Route::get('/{inspection}', [AdminVerificationController::class, 'show'])->name('show');
            Route::match(['post', 'patch'], '/{inspection}/verify', [AdminVerificationController::class, 'verify'])->name('verify');
            Route::match(['post', 'patch'], '/{inspection}/request-revision', [AdminVerificationController::class, 'requestRevision'])->name('request-revision');
            Route::match(['post', 'patch'], '/{inspection}/submit-approval', [AdminVerificationController::class, 'submitApproval'])->name('submit-approval');
        });
    });

    // Inspector Routes
    Route::middleware('role:inspector,admin,super_admin')->prefix('inspector')->as('inspector.')->group(function () {
        Route::get('/dashboard', [InspectorTaskController::class, 'dashboard'])->name('dashboard');
        Route::get('/tasks', [InspectorTaskController::class, 'index'])->name('tasks.index');
        Route::get('/history', [InspectorTaskController::class, 'history'])->name('history');

        Route::get('/tasks/{inspection}', [InspectorTaskController::class, 'show'])->name('tasks.show');
        Route::match(['post', 'patch'], '/tasks/{inspection}/start', [InspectorTaskController::class, 'start'])->name('tasks.start');
        Route::get('/tasks/{inspection}/checklist', [InspectorTaskController::class, 'checklist'])->name('tasks.checklist');
        Route::match(['post', 'put', 'patch'], '/tasks/{inspection}/save-draft', [InspectorTaskController::class, 'saveDraft'])->name('tasks.save-draft');
        Route::match(['post', 'put', 'patch'], '/tasks/{inspection}/draft', [InspectorTaskController::class, 'saveDraft'])->name('tasks.draft');
        Route::match(['post', 'put', 'patch'], '/tasks/{inspection}/submit', [InspectorTaskController::class, 'submit'])->name('tasks.submit');
        Route::post('/tasks/{inspection}/employee-signature', [InspectorTaskController::class, 'saveEmployeeSignature'])->name('tasks.employee-signature');
        Route::get('/tasks/{inspection}/revision', [InspectorTaskController::class, 'revision'])->name('tasks.revision');
        Route::match(['post', 'put', 'patch'], '/tasks/{inspection}/resubmit', [InspectorTaskController::class, 'resubmit'])->name('tasks.resubmit');

        Route::post('/tasks/{inspection}/attachments', [InspectorTaskController::class, 'uploadAttachment'])->name('tasks.attachments.upload');
        Route::delete('/attachments/{attachment}', [InspectorTaskController::class, 'deleteAttachment'])->name('tasks.attachments.delete');
    });
});
