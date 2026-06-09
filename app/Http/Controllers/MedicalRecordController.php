<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MedicalRecordController extends Controller
{
    public function create(Appointment $appointment): View
    {
        $appointment->load(['patient.user', 'doctor.user']);
        return view('doctor.medical-records.create', compact('appointment'));
    }

    public function store(Request $request, Appointment $appointment): RedirectResponse
    {
        $validated = $request->validate([
            'diagnosis' => 'required|string',
            'prescription' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $doctor = $this->authUser()->doctor;

        MedicalRecord::create([
            'appointment_id' => $appointment->id,
            'doctor_id' => $doctor->id,
            'patient_id' => $appointment->patient_id,
            'diagnosis' => $validated['diagnosis'],
            'prescription' => $validated['prescription'],
            'notes' => $validated['notes'],
        ]);

        $appointment->update(['status' => 'completed']);

        return redirect()->route('doctor.appointments')->with('success', 'تم حفظ السجل الطبي بنجاح');
    }

    public function show(MedicalRecord $record): View
    {
        $record->load(['patient.user', 'doctor.user', 'appointment']);
        return view('doctor.medical-records.show', compact('record'));
    }

    public function patientHistory($patientId): View
    {
        $patient = \App\Models\Patient::with('user')->findOrFail($patientId);
        $records = MedicalRecord::where('patient_id', $patientId)
            ->with(['doctor.user', 'appointment'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('doctor.medical-records.patient-history', compact('patient', 'records'));
    }

    public function createForPatient(Patient $patient): View
    {
        $patient->load('user');
        return view('doctor.medical-records.create-for-patient', compact('patient'));
    }

    public function storeForPatient(Request $request, Patient $patient): RedirectResponse
    {
        $validated = $request->validate([
            'diagnosis' => 'required|string',
            'prescription' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $doctor = $this->authUser()->doctor;

        MedicalRecord::create([
            'appointment_id' => null,
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'diagnosis' => $validated['diagnosis'],
            'prescription' => $validated['prescription'],
            'notes' => $validated['notes'],
        ]);

        return redirect()->route('doctor.patients.show', $patient)
            ->with('success', 'تم حفظ السجل الطبي للمريض بنجاح');
    }

    public function patientIndex(): View
    {
        $patient = $this->authUser()->patient;
        $records = MedicalRecord::where('patient_id', $patient->id)
            ->with(['doctor.user', 'appointment'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('patient.medical-records.index', compact('records'));
    }
}