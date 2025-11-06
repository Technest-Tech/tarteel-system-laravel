<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    /**
     * Display the settings page
     */
    public function index()
    {
        $supportEmail = Setting::get('support_email', '');
        $supportUsers = User::where('user_type', User::USER_TYPE['support'])->get();
        return view('admin.settings.index', compact('supportEmail', 'supportUsers'));
    }

    /**
     * Update settings
     */
    public function update(Request $request)
    {
        $request->validate([
            'support_email' => 'required|email',
        ]);

        Setting::set('support_email', $request->support_email);

        return redirect()->route('admin.settings.index')->with('success', 'تم تحديث الإعدادات بنجاح');
    }

    /**
     * Store a new support user
     */
    public function storeSupportUser(Request $request)
    {
        $validated = $request->validate([
            'user_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        try {
            User::create([
                'user_name' => $validated['user_name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'user_type' => User::USER_TYPE['support'],
            ]);

            return redirect()->route('admin.settings.index')->with('success', 'تم إضافة حساب الدعم بنجاح');
        } catch (\Exception $e) {
            return redirect()->route('admin.settings.index')
                ->with('error', 'حدث خطأ أثناء إضافة حساب الدعم: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update a support user
     */
    public function updateSupportUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        if ($user->user_type !== User::USER_TYPE['support']) {
            return redirect()->route('admin.settings.index')->with('error', 'هذا المستخدم ليس حساب دعم');
        }

        $request->validate([
            'user_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $data = [
            'user_name' => $request->user_name,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.settings.index')->with('success', 'تم تحديث حساب الدعم بنجاح');
    }

    /**
     * Delete a support user
     */
    public function deleteSupportUser($id)
    {
        $user = User::findOrFail($id);
        
        if ($user->user_type !== User::USER_TYPE['support']) {
            return redirect()->route('admin.settings.index')->with('error', 'هذا المستخدم ليس حساب دعم');
        }

        $user->delete();

        return redirect()->route('admin.settings.index')->with('success', 'تم حذف حساب الدعم بنجاح');
    }
}

