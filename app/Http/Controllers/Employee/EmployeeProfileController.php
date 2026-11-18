<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class EmployeeProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function index(Request $request): View
    {
        $employee = auth()->user()->employee;

        return view('employee.profile', [
            'employee' => $employee,
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $employee = $request->user()->employee;
        abort_unless($employee && $employee->status === 'active', 403);
        if (is_string($request->input('email'))) {
            $request->merge(['email' => mb_strtolower(trim($request->input('email')))]);
        }
        $validated = $request->validate([
            'email' => ['sometimes', 'required', 'email:filter', 'max:255',
                \Illuminate\Validation\Rule::unique('users', 'email')->ignore($request->user()->id),
                \Illuminate\Validation\Rule::unique('employees', 'email')->ignore($employee->id)],
            'phone' => ['sometimes', 'nullable', 'string', 'max:30', 'regex:/^\+?[0-9][0-9 ()-]{5,28}[0-9]$/'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);
        $photo = null;
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo')->store('employees', 'public');
            if (!$photo) throw ValidationException::withMessages(['photo' => 'មិនអាចរក្សាទុករូបបាន។ សូមសាកម្តងទៀត។']);
        }
        try {
            $oldPhoto = DB::transaction(function () use ($employee, $validated, $photo) {
                $account = \App\Models\User::whereKey($employee->user_id)->lockForUpdate()->firstOrFail();
                $profile = \App\Models\Employee::whereKey($employee->id)->lockForUpdate()->firstOrFail();
                $oldPhoto = $profile->photo;
                // Only contact details and the photo may be edited by an employee.
                if (array_key_exists('email', $validated)) {
                    $profile->email = $validated['email'];
                    if ($account->email !== $validated['email']) {
                        $account->email = $validated['email'];
                        $account->email_verified_at = null;
                        $account->save();
                    }
                }
                if (array_key_exists('phone', $validated)) $profile->phone = $validated['phone'];
                if ($photo) $profile->photo = $photo;
                $profile->save();
                return $oldPhoto;
            });
        } catch (\Throwable $error) {
            if ($photo) Storage::disk('public')->delete($photo);
            throw $error;
        }
        if ($photo && $oldPhoto && $oldPhoto !== $photo) Storage::disk('public')->delete($oldPhoto);
        return redirect()->route('employee.profile')->with('success', 'ព័ត៌មានគណនីបានរក្សាទុក។');
    }
}
