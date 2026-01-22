<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SystemSettingController extends Controller
{
    /**
     * Display system settings
     */
    public function index()
    {
        $settings = SystemSetting::orderBy('group')->orderBy('key')->get()->groupBy('group');
        
        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Update system settings
     */
    public function update(Request $request)
    {
        $settings = $request->input('settings', []);
        
        $oldValues = [];
        $newValues = [];

        foreach ($settings as $key => $value) {
            $setting = SystemSetting::where('key', $key)->first();
            
            if ($setting) {
                $oldValues[$key] = $setting->value;
                
                // Handle boolean values
                if ($setting->type === 'boolean') {
                    $value = $value ? 'true' : 'false';
                }
                
                // Handle array/json values
                if ($setting->type === 'json' || $setting->type === 'array') {
                    $value = is_array($value) ? json_encode($value) : $value;
                }
                
                $setting->update(['value' => $value]);
                $newValues[$key] = $value;
            }
        }

        // Clear settings cache
        SystemSetting::clearCache();

        AuditLog::log(
            'settings_updated',
            AuditLog::MODULE_ADMIN,
            'SystemSetting',
            null,
            null,
            $oldValues,
            $newValues,
            'Pengaturan sistem diperbarui.'
        );

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }
}
