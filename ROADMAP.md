# 🏥 Clinic Management System — Backend Development Roadmap
## نظام إدارة العيادة — خارطة طريق التطوير

---

## 📋 Executive Summary — الملخص التنفيذي

**Clinic Management System** is a full-stack Laravel application that manages appointments, medical records, and communications between three user roles: **Admin**, **Doctor**, and **Patient**.

This document outlines:

1. ✅ **Current System Architecture** — What is already built
2. 📊 **Data Models & Database Schema** — Existing tables and relationships
3. 🔌 **Missing Features & Required Endpoints** — What needs to be implemented
4. 🔐 **Authentication & Authorization** — Role-based access control
5. 🚀 **Implementation Phases** — Prioritized rollout plan
6. 🧪 **Testing Strategy** — How to validate each phase

---

## Part 1️⃣: Current System Architecture — البنية الحالية

### 1.1 Project Structure

```
clinic-app/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AdminDashboardController.php     ✅ Built
│   │   │   ├── AppointmentController.php         ✅ Built
│   │   │   ├── DoctorController.php              ✅ Built
│   │   │   ├── DoctorDashboardController.php     ✅ Built
│   │   │   ├── DoctorScheduleController.php      ✅ Built
│   │   │   ├── MedicalRecordController.php       ✅ Built
│   │   │   ├── MedicalReportController.php       ✅ Built
│   │   │   ├── PatientController.php             ✅ Built
│   │   │   ├── PatientDashboardController.php    ✅ Built
│   │   │   ├── PatientRecordController.php       ✅ Built
│   │   │   ├── ProfileController.php             ✅ Built
│   │   │   └── Auth/                             ✅ Built (Laravel Breeze)
│   │   └── Middleware/
│   │       └── RoleMiddleware.php                ✅ Built
│   ├── Models/
│   │   ├── User.php                              ✅ Built
│   │   ├── Doctor.php                            ✅ Built
│   │   ├── Patient.php                           ✅ Built
│   │   ├── Appointment.php                       ✅ Built
│   │   ├── MedicalRecord.php                     ✅ Built
│   │   ├── MedicalReport.php                     ✅ Built
│   │   ├── DoctorSchedule.php                    ✅ Built
│   │   └── AppointmentReminder.php               ⚠️  Model only — no logic
│   └── Notifications/
│       └── AppointmentStatusNotification.php     ⚠️  DB only — no email
├── resources/views/
│   ├── admin/                                    ✅ Built
│   ├── doctor/                                   ✅ Built
│   ├── patient/                                  ✅ Built
│   └── auth/                                     ✅ Built
├── routes/
│   ├── web.php                                   ✅ Built
│   └── auth.php                                  ✅ Built
├── database/migrations/                          ✅ 21 migrations
└── frontend/                                     ✅ Static HTML prototypes
```

### 1.2 Technology Stack

| Layer | Technology | Version |
|-------|-----------|---------|
| Backend Framework | Laravel | 13.x |
| Language | PHP | 8.3+ |
| Database | MySQL | 8.0+ |
| Frontend Styling | Tailwind CSS | 3.x |
| Build Tool | Vite | Latest |
| Auth Scaffolding | Laravel Breeze | Latest |
| Notifications | Laravel Notifications (DB) | Built-in |
| Queue/Jobs | Laravel Queue | ⚠️ Not configured |
| Email Driver | Laravel Mail | ⚠️ Not configured |

---

## Part 2️⃣: Data Models & Database Schema — النماذج وقاعدة البيانات

### 2.1 Existing Tables (21 Migrations)

#### Users Table
```
users
├── id               (PK, bigint)
├── name             (string, 255)
├── email            (unique, string, 255)
├── email_verified_at (timestamp, nullable)
├── password         (hashed)
├── role             (enum: admin, doctor, patient)  ← added in migration
├── remember_token   (string, nullable)
├── created_at
└── updated_at
```

#### Doctors Table
```
doctors
├── id               (PK, bigint)
├── user_id          (FK → users)
├── specialty        (string, 255)
├── license_number   (string, 255)
├── bio              (text, nullable)
├── is_active        (boolean, default: true)
├── created_at
└── updated_at
```

#### Patients Table
```
patients
├── id                   (PK, bigint)
├── user_id              (FK → users)
├── date_of_birth        (date, nullable)
├── age                  (string — computed attribute)
├── gender               (enum: male, female)
├── phone                (string, nullable)
├── address              (text, nullable)
├── blood_type           (string, nullable)
├── medical_history      (text, nullable)
├── patient_type         (enum: child, pregnant, chronic_disease, other)
├── chronic_disease_type (string, nullable)
├── chronic_disease_type2 (string, nullable)
├── created_at
└── updated_at
```

#### Appointments Table
```
appointments
├── id                   (PK, bigint)
├── patient_id           (FK → patients)
├── doctor_id            (FK → doctors)
├── appointment_date     (datetime, nullable)  ← confirmed date
├── requested_date       (datetime, nullable)  ← patient's preferred date
├── status               (enum: pending, confirmed, completed, cancelled)
├── notes                (text, nullable)
├── disease_type         (string, nullable)
├── service_category     (string, nullable)
├── patient_description  (text, nullable)
├── medicine_name        (string, nullable)
├── doctor_notes         (text, nullable)
├── created_at
└── updated_at
```

#### Medical Records Table
```
medical_records
├── id               (PK, bigint)
├── appointment_id   (FK → appointments, nullable)
├── doctor_id        (FK → doctors)
├── patient_id       (FK → patients)
├── diagnosis        (text, nullable)
├── prescription     (text, nullable)
├── notes            (text, nullable)
├── created_at
└── updated_at
```

#### Medical Reports Table
```
medical_reports
├── id               (PK, bigint)
├── patient_id       (FK → patients)
├── file_path        (string)
├── original_name    (string)
├── description      (text, nullable)
├── created_at
└── updated_at
```

#### Doctor Schedules Table
```
doctor_schedules
├── id               (PK, bigint)
├── doctor_id        (FK → doctors)
├── day              (enum: saturday, sunday, monday, tuesday, wednesday, thursday, friday)
├── start_time       (time)
├── end_time         (time)
├── is_active        (boolean, default: true)
├── created_at
└── updated_at
```

#### Appointment Reminders Table
```
appointment_reminders
├── id               (PK, bigint)
├── appointment_id   (FK → appointments)
├── reminder_type    (enum: email, sms, push)
├── status           (enum: pending, sent, failed)
├── scheduled_for    (timestamp)
├── sent_at          (timestamp, nullable)
├── error_message    (text, nullable)
├── created_at
└── updated_at
```

#### Notifications Table (Laravel Built-in)
```
notifications
├── id               (UUID, PK)
├── type             (string)
├── notifiable_type  (string)
├── notifiable_id    (bigint)
├── data             (json)
├── read_at          (timestamp, nullable)
├── created_at
└── updated_at
```

### 2.2 Model Relationships

```
User ──────── hasOne ──────────► Doctor
User ──────── hasOne ──────────► Patient

Doctor ─────── hasMany ─────────► Appointment
Doctor ─────── belongsToMany ───► Patient (via appointments)
Doctor ─────── hasMany ─────────► DoctorSchedule

Patient ─────── hasMany ─────────► Appointment
Patient ─────── hasMany ─────────► MedicalReport

Appointment ─── belongsTo ───────► Doctor
Appointment ─── belongsTo ───────► Patient
Appointment ─── hasOne ──────────► MedicalRecord
Appointment ─── hasMany ─────────► MedicalRecord
Appointment ─── hasMany ─────────► AppointmentReminder

MedicalRecord ── belongsTo ──────► Appointment (nullable)
MedicalRecord ── belongsTo ──────► Doctor
MedicalRecord ── belongsTo ──────► Patient
```

---

## Part 3️⃣: Feature Status — حالة الميزات

### 3.1 Admin Features

| Feature | Route | Status |
|---------|-------|--------|
| Admin Dashboard (stats + charts) | GET /admin/dashboard | ✅ Done |
| List all doctors | GET /doctors | ✅ Done |
| Create doctor | GET/POST /doctors/create | ✅ Done |
| Edit doctor | GET/PUT /doctors/{id}/edit | ✅ Done |
| Show doctor details | GET /doctors/{id} | ✅ Done |
| Delete doctor | DELETE /doctors/{id} | ✅ Done |
| List all patients (search + filter) | GET /patients | ✅ Done |
| Show patient details | GET /patients/{id} | ✅ Done |
| Edit patient | GET/PUT /patients/{id}/edit | ✅ Done |
| Delete patient | DELETE /patients/{id} | ✅ Done |
| View all appointments system-wide | GET /admin/appointments | ✅ Done |
| Monthly appointment analytics | Included in dashboard | ✅ Done |
| Pagination on lists | — | ❌ Missing |
| Export reports as PDF | — | ❌ Missing |
| Advanced analytics (top doctors, diseases) | — | ❌ Missing |

### 3.2 Doctor Features

| Feature | Route | Status |
|---------|-------|--------|
| Doctor Dashboard (today's stats) | GET /doctor/dashboard | ✅ Done |
| List treated patients | GET /doctor/patients | ✅ Done |
| View patient profile & history | GET /doctor/patient/{id} | ✅ Done |
| View own appointments | GET /doctor/appointments | ✅ Done |
| Create appointment for patient | GET/POST /doctor/appointments/create | ✅ Done |
| Confirm appointment (set date) | PATCH /doctor/appointments/{id}/confirm | ✅ Done |
| Complete appointment | PATCH /doctor/appointments/{id}/complete | ✅ Done |
| Cancel appointment | PATCH /doctor/appointments/{id}/cancel | ✅ Done |
| Add medicine & notes to appointment | POST /doctor/appointments/{id}/notes | ✅ Done |
| Create medical record (from appointment) | GET/POST /doctor/appointments/{id}/record | ✅ Done |
| Create medical record (standalone) | GET/POST /doctor/patients/{id}/add-record | ✅ Done |
| View patient medical history | GET /doctor/patients/{id}/history | ✅ Done |
| Manage weekly schedule | GET/POST/PATCH/DELETE /doctor/schedule | ✅ Done |
| Download patient report | GET /doctor/patient/report/{id}/download | ✅ Done |
| Conflict detection (no double-booking) | — | ❌ Missing |
| Schedule-aware booking validation | — | ❌ Missing |
| Calendar view for appointments | — | ❌ Missing |

### 3.3 Patient Features

| Feature | Route | Status |
|---------|-------|--------|
| Patient Dashboard (stats + info) | GET /patient/dashboard | ✅ Done |
| View appointments from doctors | GET /patient/appointments | ✅ Done |
| Accept appointment | PATCH /patient/appointments/{id}/accept | ✅ Done |
| Decline appointment | PATCH /patient/appointments/{id}/decline | ✅ Done |
| View medical records | GET /patient/medical-records | ✅ Done |
| View uploaded reports | GET /patient/reports | ✅ Done |
| Upload medical report (PDF/images) | POST /patient/reports/upload | ✅ Done |
| Download own report | GET /patient/reports/{id}/download | ✅ Done |
| Delete own report | DELETE /patient/reports/{id} | ✅ Done |
| Self-booking (patient books own appointment) | — | ❌ Missing |
| Search doctors by specialty or availability | — | ❌ Missing |

### 3.4 Notifications & Communication

| Feature | Status |
|---------|--------|
| DB notification on appointment confirmed | ✅ Done |
| DB notification on appointment completed | ✅ Done |
| DB notification on appointment cancelled | ✅ Done |
| Display notifications in UI (bell icon) | ❌ Missing |
| Email notification (appointment confirmed) | ❌ Missing |
| Email notification (appointment reminder) | ❌ Missing |
| Background job to send reminders | ❌ Missing |

---

## Part 4️⃣: Missing Features Specification — تفاصيل الميزات المطلوبة

### 4.1 Patient Self-Booking

**Current flow:** Doctor creates appointment → Patient accepts/declines

**Required flow:** Patient selects doctor → Sees available days → Submits booking request → Doctor confirms date

#### New Route:
```
GET  /patient/doctors              → List doctors with specialty filter
GET  /patient/doctors/{id}         → Doctor profile + available schedule
POST /patient/appointments/book    → Patient submits booking request
```

#### Controller Method — PatientBookingController:
```php
// store() — patient submits a booking request
public function store(Request $request)
{
    $validated = $request->validate([
        'doctor_id'          => 'required|exists:doctors,id',
        'requested_date'     => 'required|date|after:today',
        'disease_type'       => 'required|string',
        'service_category'   => 'required|string',
        'patient_description'=> 'nullable|string',
    ]);

    // Check: no existing pending appointment with same doctor
    $existing = Appointment::where('patient_id', auth()->user()->patient->id)
        ->where('doctor_id', $validated['doctor_id'])
        ->whereIn('status', ['pending', 'confirmed'])
        ->exists();

    if ($existing) {
        return back()->withErrors(['error' => 'لديك موعد نشط مع هذا الطبيب']);
    }

    Appointment::create([
        ...$validated,
        'patient_id' => auth()->user()->patient->id,
        'status'     => 'pending',
    ]);

    return redirect()->route('patient.appointments')->with('success', 'تم إرسال طلب الحجز');
}
```

---

### 4.2 Appointment Conflict Detection

**Problem:** Doctor can currently confirm two appointments at the same date/time.

#### Add to AppointmentController::confirm():
```php
// Before saving, check for conflicts
$conflict = Appointment::where('doctor_id', $appointment->doctor_id)
    ->where('appointment_date', $request->appointment_date)
    ->where('status', 'confirmed')
    ->where('id', '!=', $appointment->id)
    ->exists();

if ($conflict) {
    return back()->withErrors(['error' => 'هذا الوقت محجوز بالفعل، اختر وقتاً آخر']);
}
```

---

### 4.3 Schedule-Aware Booking

**Problem:** Booking ignores the doctor's working schedule entirely.

#### New migration needed:
```php
// No new migration needed — doctor_schedules table already exists
// Just add validation in AppointmentController::confirm()

$dayName = strtolower(Carbon::parse($request->appointment_date)->englishDayOfWeek);

$scheduleExists = DoctorSchedule::where('doctor_id', $appointment->doctor_id)
    ->where('day', $dayName)
    ->where('is_active', true)
    ->exists();

if (!$scheduleExists) {
    return back()->withErrors(['error' => 'الطبيب لا يعمل في هذا اليوم']);
}
```

---

### 4.4 Pagination

**Problem:** All list pages load all records with no pagination.

#### Add to every index() method:
```php
// AdminDashboardController, DoctorController, PatientController, AppointmentController

// BEFORE (no pagination):
$doctors = Doctor::with('user')->get();

// AFTER (with pagination):
$doctors = Doctor::with('user')->paginate(15);
```

#### Update all views to include pagination links:
```blade
{{-- Add at bottom of every table view --}}
{{ $doctors->links() }}
```

---

### 4.5 Email Notifications

**Problem:** `AppointmentReminder` table exists but no emails are sent.

#### Step 1 — Configure .env:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@clinic.com
MAIL_FROM_NAME="عيادة الصحة"
```

#### Step 2 — Update AppointmentStatusNotification.php:
```php
public function via(object $notifiable): array
{
    return ['database', 'mail'];  // Add 'mail' channel
}

public function toMail(object $notifiable): MailMessage
{
    return (new MailMessage)
        ->subject('تحديث حالة موعدك')
        ->line($this->getMessage())
        ->action('عرض الموعد', url('/patient/appointments'));
}
```

#### Step 3 — Create Reminder Job:
```
php artisan make:job SendAppointmentReminder
```

```php
// app/Jobs/SendAppointmentReminder.php
class SendAppointmentReminder implements ShouldQueue
{
    public function handle(): void
    {
        $tomorrow = Carbon::tomorrow();

        $appointments = Appointment::with(['patient.user', 'doctor.user'])
            ->where('status', 'confirmed')
            ->whereDate('appointment_date', $tomorrow)
            ->get();

        foreach ($appointments as $appointment) {
            $appointment->patient->user->notify(
                new AppointmentStatusNotification($appointment, 'reminder')
            );
        }
    }
}
```

#### Step 4 — Schedule the job in routes/console.php:
```php
Schedule::job(new SendAppointmentReminder)->dailyAt('08:00');
```

---

### 4.6 Notifications Bell in UI

**Problem:** Notifications are saved to DB but never shown to users.

#### Add to layouts/app.blade.php:
```blade
{{-- Bell icon in navbar --}}
@auth
<div class="relative">
    <button id="notif-btn">
        🔔
        @if(auth()->user()->unreadNotifications->count() > 0)
            <span class="badge">{{ auth()->user()->unreadNotifications->count() }}</span>
        @endif
    </button>

    <div id="notif-dropdown" class="hidden">
        @forelse(auth()->user()->unreadNotifications->take(5) as $notif)
            <div>{{ $notif->data['message'] }}</div>
        @empty
            <div>لا توجد إشعارات جديدة</div>
        @endforelse
    </div>
</div>
@endauth
```

#### New route to mark as read:
```php
Route::post('/notifications/read-all', function () {
    auth()->user()->unreadNotifications->markAsRead();
    return back();
})->name('notifications.readAll');
```

---

### 4.7 Calendar View for Appointments

**Required:** Visual calendar showing doctor's appointments by day.

#### Recommended package:
```bash
# Use FullCalendar.js (free, open-source)
# No composer package needed — include via CDN in the view
```

#### New view: doctor/appointments/calendar.blade.php
```blade
{{-- Pass appointments as JSON to FullCalendar --}}
<div id="calendar"></div>

<script>
const appointments = @json($appointments->map(fn($a) => [
    'title'  => $a->patient->user->name,
    'start'  => $a->appointment_date,
    'color'  => match($a->status) {
        'confirmed' => '#22c55e',
        'pending'   => '#f59e0b',
        'cancelled' => '#ef4444',
        default     => '#6b7280',
    },
]));
</script>
```

---

### 4.8 Patient Doctor Search

**Required:** Patient can browse doctors by specialty and see available days.

#### New routes:
```
GET /patient/doctors              → List all active doctors (filter by specialty)
GET /patient/doctors/{id}         → Doctor profile + working schedule
GET /api/doctors/{id}/schedule    → JSON: available days for booking (AJAX)
```

#### DoctorScheduleController::getAvailableDoctors() already exists:
```php
// Already built — just needs to be wired to patient views
public function getAvailableDoctors(Request $request)
{
    // Returns JSON list of doctors available on a given day
}
```

---

## Part 5️⃣: Authentication & Authorization — المصادقة والصلاحيات

### 5.1 Current Implementation

Authentication is handled by **Laravel Breeze** with email verification.

```
Registration → Email Verification → Login → Role-based Redirect
```

### 5.2 Role-Based Access Control (RBAC)

```php
// app/Http/Middleware/RoleMiddleware.php — Already built
// Usage in routes:
Route::middleware(['role:admin'])->group(...);
Route::middleware(['role:doctor'])->group(...);
Route::middleware(['role:patient'])->group(...);
```

### 5.3 Access Matrix

| Feature | Admin | Doctor | Patient |
|---------|-------|--------|---------|
| View all appointments | ✅ | ❌ | ❌ |
| Manage doctors (CRUD) | ✅ | ❌ | ❌ |
| Manage patients (CRUD) | ✅ | ❌ | ❌ |
| Create appointment | ❌ | ✅ | 🔜 (Phase 1) |
| Confirm/cancel appointment | ❌ | ✅ | ❌ |
| Accept/decline appointment | ❌ | ❌ | ✅ |
| Create medical records | ❌ | ✅ | ❌ |
| View own medical records | ❌ | ❌ | ✅ |
| Upload medical reports | ❌ | ❌ | ✅ |
| Download patient reports | ❌ | ✅ | ✅ |
| Manage own schedule | ❌ | ✅ | ❌ |
| View system statistics | ✅ | ❌ | ❌ |

---

## Part 6️⃣: Implementation Phases — مراحل التنفيذ

### Phase 1️⃣: Critical Fixes — الإصلاحات الأساسية
**Priority: CRITICAL — يجب إنجازها قبل أي عرض**
**Estimated Time: 1 Week**

- [ ] Add pagination to all list views (doctors, patients, appointments)
- [ ] Add appointment conflict detection in `AppointmentController::confirm()`
- [ ] Add schedule-aware validation when confirming appointment dates
- [ ] Fix missing input validation in appointment creation forms
- [ ] Add CSRF protection check on all forms (verify existing forms are protected)

**Deliverable:** Stable, bug-free core system

---

### Phase 2️⃣: Patient Self-Booking — حجز المريض بنفسه
**Priority: HIGH — الأهم وظيفياً**
**Estimated Time: 1 Week**

- [ ] Create `PatientBookingController.php`
- [ ] Add routes: `GET /patient/doctors`, `GET /patient/doctors/{id}`, `POST /patient/appointments/book`
- [ ] Create views: `patient/doctors/index.blade.php`, `patient/doctors/show.blade.php`
- [ ] Create view: `patient/appointments/book.blade.php`
- [ ] Wire `DoctorScheduleController::getAvailableDoctors()` to patient booking flow
- [ ] Add duplicate booking guard (one active appointment per doctor per patient)

**Deliverable:** Patient can self-book appointments

---

### Phase 3️⃣: Notifications & Emails — الإشعارات والبريد
**Priority: HIGH**
**Estimated Time: 1 Week**

- [ ] Configure mail driver in `.env`
- [ ] Update `AppointmentStatusNotification` to send emails via `mail` channel
- [ ] Create `SendAppointmentReminder` job
- [ ] Schedule reminder job in `routes/console.php` (daily at 8:00 AM)
- [ ] Add notification bell icon to `layouts/app.blade.php`
- [ ] Add route `POST /notifications/read-all`
- [ ] Test email delivery end-to-end

**Deliverable:** Users receive email confirmations and reminder notifications

---

### Phase 4️⃣: Calendar View — عرض التقويم
**Priority: MEDIUM**
**Estimated Time: 3–4 Days**

- [ ] Add FullCalendar.js CDN to doctor appointment views
- [ ] Create `doctor/appointments/calendar.blade.php`
- [ ] Pass appointments as JSON from controller to calendar
- [ ] Add toggle button: List View / Calendar View
- [ ] Color-code appointments by status (pending=yellow, confirmed=green, cancelled=red)

**Deliverable:** Doctor can view appointments on a visual calendar

---

### Phase 5️⃣: Advanced Features — الميزات المتقدمة
**Priority: MEDIUM**
**Estimated Time: 2 Weeks**

- [ ] **PDF Export:** Export medical records and appointment summaries as PDF
  ```bash
  composer require barryvdh/laravel-dompdf
  ```
- [ ] **Advanced Admin Analytics:** Charts for top doctors, most common diseases, appointment trends
- [ ] **Patient Doctor Search:** Search by specialty, filter by availability, sort by rating
- [ ] **Activity Log:** Track who changed what and when
  ```bash
  composer require spatie/laravel-activitylog
  ```

**Deliverable:** Complete reporting and search features

---

### Phase 6️⃣: Multi-Language & API — اللغات المتعددة والـ API
**Priority: LOW — مستقبلي**
**Estimated Time: 2–3 Weeks**

- [ ] **Proper i18n:** Extract all Arabic hard-coded strings to `lang/ar/` files
- [ ] **Language Switcher:** Toggle between Arabic and English in UI
- [ ] **REST API Endpoints:** Build API layer for future mobile app support
  ```bash
  composer require laravel/sanctum
  ```
- [ ] **Two-Factor Authentication (2FA)**

**Deliverable:** Internationalized app ready for mobile expansion

---

### Phase 7️⃣: Mobile Application — تطبيق الجوال
**Priority: FUTURE — بعد التخرج**

- [ ] Build REST API from Phase 6
- [ ] Develop mobile app (React Native / Flutter) consuming the API
- [ ] Push notifications via Firebase (FCM)
- [ ] Online payment integration

---

## Part 7️⃣: Technical Requirements — المتطلبات التقنية

### 7.1 Environment Configuration

```env
# .env — Required settings

APP_NAME="نظام إدارة العيادة"
APP_ENV=local
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=clinic_db
DB_USERNAME=root
DB_PASSWORD=

# Mail — required for Phase 3
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@clinic.com
MAIL_FROM_NAME="عيادة الصحة"

# Queue — required for Phase 3
QUEUE_CONNECTION=database
```

### 7.2 New Packages to Install (by Phase)

```bash
# Phase 3 — Email & Queue
# No new packages needed (Laravel Mail + Queue are built-in)

# Phase 4 — Calendar
# No package needed (FullCalendar.js via CDN)

# Phase 5 — PDF Export
composer require barryvdh/laravel-dompdf

# Phase 5 — Activity Logging
composer require spatie/laravel-activitylog

# Phase 6 — API Authentication
composer require laravel/sanctum
```

### 7.3 New Controllers to Create

```
app/Http/Controllers/
├── PatientBookingController.php        ← Phase 2
├── NotificationController.php          ← Phase 3
└── Api/                                ← Phase 6
    ├── AuthController.php
    ├── AppointmentController.php
    ├── DoctorController.php
    └── PatientController.php
```

### 7.4 New Migrations Needed

```bash
# Phase 1 — no new migrations

# Phase 2 — no new migrations (appointments table already supports patient_id + status)

# Phase 5 — Activity log
php artisan activitylog:publish  # publishes migration automatically
```

### 7.5 Security Checklist

- [x] CSRF protection on all forms (Laravel default)
- [x] Password hashing via `bcrypt` (Laravel default)
- [x] Role-based route middleware (`RoleMiddleware`)
- [x] Email verification before dashboard access
- [ ] Rate limiting on login endpoint
- [ ] File upload validation (MIME type + size check) — partially done
- [ ] Prevent patients from accessing other patients' records
- [ ] Prevent doctors from accessing other doctors' records

#### Add rate limiting to auth routes (routes/auth.php):
```php
Route::post('/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware('throttle:5,1')  // 5 attempts per minute
    ->name('login');
```

---

## Part 8️⃣: Testing Strategy — استراتيجية الاختبار

### 8.1 Existing Tests

```
tests/
├── Feature/
│   ├── Auth/
│   │   ├── AuthenticationTest.php       ✅ Exists
│   │   ├── EmailVerificationTest.php    ✅ Exists
│   │   ├── PasswordConfirmationTest.php ✅ Exists
│   │   ├── PasswordResetTest.php        ✅ Exists
│   │   ├── PasswordUpdateTest.php       ✅ Exists
│   │   └── RegistrationTest.php         ✅ Exists
│   └── ProfileTest.php                  ✅ Exists
└── Unit/                                ❌ Empty — needs tests
```

### 8.2 Tests to Write Per Phase

#### Phase 1 — Conflict Detection:
```php
// tests/Feature/AppointmentConflictTest.php
public function test_doctor_cannot_confirm_two_appointments_at_same_time()
{
    // Create two appointments for same doctor, same datetime
    // Confirm first → should succeed
    // Confirm second → should fail with error
}
```

#### Phase 2 — Patient Self-Booking:
```php
// tests/Feature/PatientBookingTest.php
public function test_patient_can_book_appointment()
public function test_patient_cannot_book_duplicate_appointment()
public function test_patient_cannot_book_on_doctor_day_off()
```

#### Phase 3 — Notifications:
```php
// tests/Feature/NotificationTest.php
public function test_patient_receives_notification_when_appointment_confirmed()
public function test_patient_receives_email_when_appointment_confirmed()
```

### 8.3 Run Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test --filter AppointmentConflictTest

# Run with coverage report
php artisan test --coverage
```

---

## 📊 Phase Summary — ملخص المراحل

```
Phase 1  →  Phase 2  →  Phase 3  →  Phase 4  →  Phase 5  →  Phase 6  →  Phase 7
 1 Week      1 Week      1 Week      4 Days       2 Weeks      3 Weeks    Future

Critical    Patient     Emails &    Calendar    PDF/Search    API &        Mobile
 Fixes      Booking    Notifs      View        Analytics     i18n          App
```

| Phase | Priority | Time | Deliverable |
|-------|----------|------|-------------|
| 1 - Critical Fixes | 🔴 Critical | 1 week | Stable, demo-ready system |
| 2 - Patient Booking | 🔴 High | 1 week | Complete booking workflow |
| 3 - Notifications | 🟠 High | 1 week | Email + reminder system |
| 4 - Calendar View | 🟡 Medium | 4 days | Visual appointment calendar |
| 5 - Advanced Features | 🟡 Medium | 2 weeks | PDF export, analytics, search |
| 6 - API & i18n | 🟢 Low | 3 weeks | Mobile-ready API |
| 7 - Mobile App | ⚪ Future | — | iOS/Android application |

---

## 📞 Project Information

**Project:** Clinic Management System — نظام إدارة العيادة
**Framework:** Laravel 13 / PHP 8.3
**Repository:** https://github.com/farahraedrasheed/GraduatuionProject
**Stack:** Laravel · MySQL · Tailwind CSS · Vite · Laravel Breeze

---

## 📝 Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | 2026-06-09 | Initial full roadmap |
