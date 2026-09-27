<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\FormConfiguration;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class FormConfigurationController extends Controller
{
    public function index(): View
    {
        $config = FormConfiguration::current();
        return view('super-admin.form-configuration.index', compact('config'));
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'company_name' => ['required', 'string', 'max:200'],
            'department_name' => ['required', 'string', 'max:150'],
            'form_number' => ['required', 'string', 'max:100'],
            'publication_date' => ['required', 'date'],
            'revision_status' => ['required', 'string', 'max:50'],
            'page_label' => ['nullable', 'string', 'max:30'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
        ]);

        $config = FormConfiguration::current();
        $oldValues = $config->toArray();

        $data = $request->only([
            'company_name',
            'department_name',
            'form_number',
            'publication_date',
            'revision_status',
            'page_label',
        ]);
        $data['updated_by'] = Auth::id();

        if ($request->hasFile('logo')) {
            if ($config->logo_path && Storage::disk('public')->exists($config->logo_path)) {
                Storage::disk('public')->delete($config->logo_path);
            }
            $logoName = 'logos/' . Str::random(40) . '.' . $request->file('logo')->getClientOriginalExtension();
            $path = $request->file('logo')->storeAs('logos', basename($logoName), 'public');
            $data['logo_path'] = $path;
        }

        $config->update($data);

        ActivityLogService::log(
            'ubah data',
            'Konfigurasi Formulir',
            'Memperbarui konfigurasi header dan metadata formulir standar KAI',
            $config,
            $oldValues,
            $data
        );

        return back()->with('success', 'Konfigurasi formulir berhasil disimpan.');
    }
}
