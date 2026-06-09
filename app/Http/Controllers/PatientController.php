<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PatientController extends Controller
{
    public function index(Request $request): View
    {
        $query = Patient::with('user');
        
        if ($request->search) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->gender) {
            $query->where('gender', $request->gender);
        }

        $patients = $query->orderBy('created_at', 'desc')->paginate(15);
        return view('admin.patients.index', compact('patients'));
    }

    public function show(Patient $patient): View
    {
        $patient->load(['user', 'appointments.doctor.user']);
        return view('admin.patients.show', compact('patient'));
    }

    public function edit(Patient $patient): View
    {
        $patient->load('user');
        return view('admin.patients.edit', compact('patient'));
    }

    public function update(Request $request, Patient $patient): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female',
            'blood_type' => 'nullable|string',
            'address' => 'nullable|string',
            'medical_history' => 'nullable|string',
        ]);

        $patient->user->update(['name' => $validated['name']]);
        $patient->update($validated);

        return redirect()->route('patients.index')->with('success', 'تم تحديث بيانات المريض');
    }

    public function destroy(Patient $patient): RedirectResponse
    {
        $patient->user->delete();
        $patient->delete();

        return redirect()->route('patients.index')->with('success', 'تم حذف المريض');
    }
}