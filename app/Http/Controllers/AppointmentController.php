<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Notifications\AppointmentStatusNotification;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function index(Request $request): View
    {
        $appointments = Appointment::with(['patient.user', 'doctor.user'])
            ->orderBy('appointment_date', 'desc')
            ->paginate(15);

        return view('admin.appointments.index', compact('appointments'));
    }

    public function doctorIndex(Request $request): View
    {
        $doctor = $this->authUser()->doctor;

        $query = Appointment::where('doctor_id', $doctor->id)
            ->with('patient.user');
            
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('patient.user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $appointments = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('doctor.appointments.index', compact('appointments', 'doctor'));
    }

    public function doctorCreate(Request $request): View
    {
        $doctor = $this->authUser()->doctor;

        $treatedPatientIds = Appointment::where('doctor_id', $doctor->id)
            ->distinct()
            ->pluck('patient_id')
            ->toArray();
        
        if (empty($treatedPatientIds)) {
            $patients = collect();
        } else {
            $patients = \App\Models\Patient::whereIn('id', $treatedPatientIds)
                ->get()
                ->map(function($p) {
                    $p->user_name = $p->user->name ?? '';
                    return $p;
                })
                ->sortBy('user_name')
                ->values();
        }
        
        return view('doctor.appointments.create', compact('patients', 'doctor'));
    }

    public function doctorStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'requested_date' => 'required|date|after:today',
            'disease_type' => 'required|string',
            'patient_description' => 'nullable|string',
            'prescribed_medications' => 'nullable|string',
            'medicine_name' => 'nullable|string',
            'doctor_notes' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $doctor = $this->authUser()->doctor;

        Appointment::create([
            'patient_id' => $validated['patient_id'],
            'doctor_id' => $doctor->id,
            'requested_date' => $validated['requested_date'],
            'appointment_date' => null,
            'disease_type' => $validated['disease_type'],
            'patient_description' => $validated['patient_description'] ?? null,
            'medicine_name' => $validated['medicine_name'] ?? null,
            'doctor_notes' => $validated['doctor_notes'] ?? null,
            'notes' => $validated['notes'],
            'status' => 'pending',
        ]);

        return back()->with('success', 'تم إرسال طلب الموعد للمريض');
    }

    public function patientAppointments(Request $request): View
    {
        $patient = $this->authUser()->patient;
        
        $appointments = Appointment::where('patient_id', $patient->id)
            ->with('doctor.user', 'medicalRecords')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('patient.appointments.index', compact('appointments'));
    }

    public function acceptAppointment(Appointment $appointment): RedirectResponse
    {
        $appointment->update(['status' => 'confirmed']);
        return back()->with('success', 'تم قبول الموعد');
    }

    public function declineAppointment(Appointment $appointment): RedirectResponse
    {
        $appointment->update(['status' => 'cancelled']);
        return back()->with('success', 'تم رفض الموعد');
    }

    public function create(Request $request): View
    {
        $doctors = Doctor::where('is_active', true)->with('user')->get();
        return view('patient.appointments.create', compact('doctors'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'requested_date' => 'required|date|after:today',
            'notes' => 'nullable|string',
        ]);

        $doctor = $this->authUser()->doctor;

        Appointment::create([
            'patient_id' => $validated['patient_id'],
            'doctor_id' => $doctor->id,
            'requested_date' => $validated['requested_date'],
            'appointment_date' => null,
            'notes' => $validated['notes'],
            'status' => 'pending',
        ]);

        return back()->with('success', 'تم إرسال طلب الموعد للمريض');
    }

    public function confirm(Request $request, Appointment $appointment): RedirectResponse
    {
        $validated = $request->validate([
            'appointment_date' => 'required|date|after:now',
        ]);

        $date = Carbon::parse($validated['appointment_date']);
        $dayName = strtolower($date->englishDayOfWeek);

        $scheduleExists = DoctorSchedule::where('doctor_id', $appointment->doctor_id)
            ->where('day', $dayName)
            ->where('is_active', true)
            ->exists();

        if (!$scheduleExists) {
            return back()->withErrors(['appointment_date' => 'الطبيب لا يعمل في هذا اليوم، اختر يوماً آخر']);
        }

        $conflict = Appointment::where('doctor_id', $appointment->doctor_id)
            ->where('appointment_date', $validated['appointment_date'])
            ->where('status', 'confirmed')
            ->where('id', '!=', $appointment->id)
            ->exists();

        if ($conflict) {
            return back()->withErrors(['appointment_date' => 'هذا الوقت محجوز بالفعل، اختر وقتاً آخر']);
        }

        $appointment->update([
            'appointment_date' => $validated['appointment_date'],
            'status' => 'confirmed',
        ]);

        $appointment->patient->user->notify(new AppointmentStatusNotification($appointment, 'confirmed'));

        return back()->with('success', 'تم تأكيد الموعد');
    }

    public function complete(Request $request, Appointment $appointment): RedirectResponse
    {
        $appointment->update(['status' => 'completed']);
        $appointment->patient->user->notify(new AppointmentStatusNotification($appointment, 'completed'));
        return back()->with('success', 'تم إكمال الموعد');
    }

    public function cancel(Request $request, Appointment $appointment): RedirectResponse
    {
        $appointment->update(['status' => 'cancelled']);
        $appointment->patient->user->notify(new AppointmentStatusNotification($appointment, 'cancelled'));
        return back()->with('success', 'تم إلغاء الموعد');
    }

    public function addNotes(Request $request, Appointment $appointment): RedirectResponse
    {
        $validated = $request->validate([
            'medicine_name' => 'nullable|string',
            'doctor_notes' => 'nullable|string',
        ]);

        $appointment->update($validated);
        $appointment->patient->user->notify(new AppointmentStatusNotification($appointment, 'completed'));

        return back()->with('success', 'تم حفظ الملاحظات');
    }

    public function updateStatus(Request $request, Appointment $appointment): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:confirmed,completed,cancelled',
        ]);

        $appointment->update(['status' => $validated['status']]);

        $appointment->patient->user->notify(new AppointmentStatusNotification($appointment, $validated['status']));

        return back()->with('success', 'تم تحديث حالة الموعد');
    }
}