<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\View\View;

class PatientDashboardController extends Controller
{
    public function index(): View
    {
        $patient = $this->authUser()->patient;
        
        $appointments = Appointment::where('patient_id', $patient->id)
            ->with('doctor.user')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $stats = [
            'pending' => Appointment::where('patient_id', $patient->id)
                ->where('status', 'pending')
                ->count(),
            'confirmed' => Appointment::where('patient_id', $patient->id)
                ->where('status', 'confirmed')
                ->count(),
            'completed' => Appointment::where('patient_id', $patient->id)
                ->where('status', 'completed')
                ->count(),
        ];

        return view('patient.dashboard', compact('appointments', 'stats', 'patient'));
    }
}