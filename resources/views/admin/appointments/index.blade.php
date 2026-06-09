@extends('layouts.app')

@section('title', 'إدارة المواعيد - المركز الطبي')

@section('content')
    <div style="max-width: 1200px; margin: 0 auto;">
        <div style="background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 1.5rem;">
            <h2 style="font-size: 1.25rem; font-weight: 700; color: #1f2937; margin-bottom: 1rem;">جميع المواعيد</h2>

            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="text-align: right; color: #6b7280; border-bottom: 1px solid #e5e7eb;">
                        <th style="padding: 0.75rem 1rem;">المريض</th>
                        <th style="padding: 0.75rem 1rem;">الطبيب</th>
                        <th style="padding: 0.75rem 1rem;">التاريخ</th>
                        <th style="padding: 0.75rem 1rem;">الحالة</th>
                        <th style="padding: 0.75rem 1rem;">ملاحظات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appointments as $appointment)
                    <tr style="border-bottom: 1px solid #e5e7eb;">
                        <td style="padding: 0.75rem 1rem;">{{ $appointment->patient->user->name }}</td>
                        <td style="padding: 0.75rem 1rem;">د. {{ $appointment->doctor->user->name }}</td>
                        <td style="padding: 0.75rem 1rem;">{{ $appointment->appointment_date ? $appointment->appointment_date->format('Y-m-d H:i') : '-' }}</td>
                        <td style="padding: 0.75rem 1rem;">
                            <span class="badge 
                                @if($appointment->status == 'pending') badge-pending
                                @elseif($appointment->status == 'confirmed') badge-confirmed
                                @elseif($appointment->status == 'completed') badge-completed
                                @else badge-cancelled @endif">
                                @if($appointment->status == 'pending') معلق
                                @elseif($appointment->status == 'confirmed') مؤكد
                                @elseif($appointment->status == 'completed') مكتمل
                                @else ملغي @endif
                            </span>
                        </td>
                        <td style="padding: 0.75rem 1rem; color: #6b7280; font-size: 0.875rem;">{{ $appointment->notes ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="padding: 2rem 0; text-align: center; color: #6b7280;">لا توجد مواعيد</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            @if($appointments->hasPages())
            <div style="padding: 1rem;">
                {{ $appointments->links() }}
            </div>
            @endif
        </div>
    </div>
@endsection
