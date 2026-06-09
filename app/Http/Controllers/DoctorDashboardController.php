<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\View\View;

class DoctorDashboardController extends Controller
{
    public function index(): View
    {
        $doctor = $this->authUser()->doctor;
        
        $appointments = Appointment::where('doctor_id', $doctor->id)
            ->with('patient.user')
            ->orderBy('appointment_date', 'desc')
            ->take(10)
            ->get();

        $stats = [
            'today' => Appointment::where('doctor_id', $doctor->id)
                ->whereDate('appointment_date', today())
                ->count(),
            'pending' => Appointment::where('doctor_id', $doctor->id)
                ->where('status', 'pending')
                ->count(),
            'confirmed' => Appointment::where('doctor_id', $doctor->id)
                ->where('status', 'confirmed')
                ->count(),
            'completed' => Appointment::where('doctor_id', $doctor->id)
                ->where('status', 'completed')
                ->count(),
            'total' => Appointment::where('doctor_id', $doctor->id)
                ->count(),
        ];

        $today_appointments = Appointment::where('doctor_id', $doctor->id)
            ->whereDate('appointment_date', today())
            ->with('patient.user')
            ->orderBy('appointment_date')
            ->get();

        return view('doctor.dashboard', compact('appointments', 'stats', 'doctor', 'today_appointments'));
    }
}