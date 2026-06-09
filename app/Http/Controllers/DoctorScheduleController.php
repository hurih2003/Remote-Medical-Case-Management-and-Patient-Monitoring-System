<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\DoctorSchedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DoctorScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $doctor = $this->authUser()->doctor;
        $schedules = DoctorSchedule::where('doctor_id', $doctor->id)
            ->orderByRaw("FIELD(day, 'saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday')")
            ->get();

        return view('doctor.schedule.index', compact('schedules', 'doctor'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'day' => 'required|in:saturday,sunday,monday,tuesday,wednesday,thursday,friday',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
        ]);

        $doctor = $this->authUser()->doctor;

        DoctorSchedule::create([
            'doctor_id' => $doctor->id,
            'day' => $validated['day'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
        ]);

        return back()->with('success', 'تم إضافة موعد العمل بنجاح');
    }

    public function update(Request $request, DoctorSchedule $schedule): RedirectResponse
    {
        $validated = $request->validate([
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'is_active' => 'boolean',
        ]);

        $schedule->update($validated);

        return back()->with('success', 'تم تحديث موعد العمل');
    }

    public function destroy(DoctorSchedule $schedule): RedirectResponse
    {
        $schedule->delete();
        return back()->with('success', 'تم حذف موعد العمل');
    }

    public function getAvailableDoctors(Request $request)
    {
        $day = $request->input('day');
        
        $doctors = Doctor::where('is_active', true)
            ->whereHas('schedules', function($query) use ($day) {
                $query->where('day', $day)->where('is_active', true);
            })
            ->with('user')
            ->get();

        return response()->json($doctors);
    }
}