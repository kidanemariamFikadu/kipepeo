# Volunteer Management (full PRD: MVP + V2)

## Context

`docs/volunteer-management-prd.md` defines a new Volunteer Management feature: staff can register volunteers, check them in/out (mirroring student attendance), log what they did during a visit (activity type + optional student(s) + notes), and see reporting on hours/activity counts. The data layer (6 migrations, 6 models, `Student::volunteerActivities()`) is already built and verified against the PRD in a prior review — this plan covers everything on top of it: the actual CRUD UI, check-in/out flow, activity logging, reporting, and the Student Detail integration, combining the PRD's MVP and V2 phases into one implementation pass per explicit request.

The very first implementation step is saving this plan to `docs/volunteer-management-implementation-plan.md` so it lives alongside the PRD as a permanent repo artifact, not just a session-local plan file.

## Already built (foundation, not part of this plan)

Migrations (all applied in dev): `volunteers`, `activity_types`, `volunteer_attendances`, `volunteer_attendance_attrs`, `volunteer_activities` (soft-deletes), `volunteer_activity_student` (pivot, composite PK, has timestamps). Models: `App\Models\Volunteer`, `ActivityType`, `VolunteerAttendance`, `VolunteerAttendanceAttr`, `VolunteerActivity` (SoftDeletes, relations to attendance/volunteer/activityType/students), and `Student::volunteerActivities(): BelongsToMany`.

**Key constraint carried into this plan**: `VolunteerActivity` has no duration/hours column. "Hours" only exists as `VolunteerAttendance.total_time` (seconds, per visit). Reporting must sum hours from attendances and count activities from activities — never conflate the two.

## Patterns being mirrored (cite these, don't redesign)

- **Check-in/out**: `app/Livewire/Attendance/AttendanceStudent.php` — `checkIn($id)` finds-or-creates today's attendance row, guards double-checkin (`if ($attendance->current_in) { flash error; return; }`), creates a new `*Attr` row with `time_in`. `checkOut($id)` closes the open `*Attr` row, accumulates `total_time += diffInSeconds(time_in)`. Blade: searchable/sortable/paginated table, one action button per row, `wire:loading` + `<x-spinner>`.
- **Admin CRUD**: `app/Livewire/Setting/GradeList.php` + `Grade.php` (modal `ModalComponent`, `Rule::unique(...)->ignore($id)` for edits, `#[On('x-changed')]` no-op re-render listener, delete guard via `->relation()->exists()` dispatching `MessageChanged`). Wired into `resources/views/livewire/setting/index.blade.php` as a new `grid-cols-1 lg:grid-cols-2` row — Settings is one scrolling page, not tabs. `/settings` is already admin-gated by route middleware, so no `abort_unless` needed in these components.
- **Report tab**: `app/Livewire/Report/AlumniReport.php` + blade — `mount()` calls `filter()`, `filter()` validates a date range + optional id, `render()` re-queries fresh with `->when(...)` chains. New tab = one `<li>` button + one `<div id="styled-x">` panel appended to `resources/views/livewire/report/index.blade.php` (Flowbite auto-discovers `data-tabs-target`, no JS registration).
- **Student Detail card**: `app/Livewire/Student/StudentDetail.php::loadStudent()` (called by both `mount()` and `render()`) is the single place to add an eager load. Card markup mirrors the "Guardian Information" card in `student-detail.blade.php` (header/table/empty-state), minus the add-button and actions column since this new card is read-only.

## Resolved design decisions

1. **Enum → Blade select**: iterate directly, `@foreach (\App\Enums\ActivityCategory::cases() as $case)` — no mapper helper needed, nothing like this exists elsewhere in the codebase either.
2. **Activity logging entry point**: a "Log Activity" modal button shown next to "Check out" whenever a volunteer is currently checked in (not merged into checkout). Student picker is a native `<select multiple wire:model="studentIds">` over `Student::active()` — no new UI library.
3. **Seeding**: `database/seeders/ActivityTypeSeeder.php` creates the 3 PRD-named types (Tutoring/tutoring, Extracurricular Training/extracurricular, Mentorship/mentorship), registered in `DatabaseSeeder::run()` via `$this->call(ActivityTypeSeeder::class);` (same pattern as the existing 5 `$this->call(...)` lines).
4. **Active filtering**: add `Volunteer::scopeActive()` (mirrors `Student::scopeActive()`) and use it to filter the check-in/out roster only. The Settings roster and all reporting stay unfiltered by status (admins need to see/reactivate inactive volunteers; history must never disappear).
5. **Nav placement**: `/volunteers` goes in `$navLinks` (open to all staff, same tier as Attendance) in `resources/views/components/layouts/app.blade.php`, right after the Attendance entry. Volunteer roster and Activity Type CRUD get no separate nav entry — they live inside `/settings`, same as Grades/Schools.
6. **Hours vs. activity counts**: two independent aggregates in the report — hours summed from `VolunteerAttendance.total_time` grouped by volunteer; activity counts from `VolunteerActivity` grouped by `activity_type_id`. A per-volunteer filter additionally surfaces the raw activity log (date/type/students/notes) for that volunteer, covering the PRD's "history ... per volunteer" requirement.

Two small additions to already-built files fall out of this: `Volunteer::scopeActive()` + `Volunteer::secondsToHms()` (copy of `Student::secondsToHms()`) in `app/Models/Volunteer.php`; and `->withTimestamps()` on `VolunteerActivity::students()` / `Student::volunteerActivities()` since that pivot uniquely has timestamp columns.

## Implementation

**1. Volunteer roster admin CRUD** — `app/Livewire/Setting/VolunteerList.php` (`#[Computed] getVolunteerListProperty()` → `Volunteer::search($this->search)->orderBy('name')->paginate(20)`, unfiltered by status; no delete method — PRD scopes this to create/edit/deactivate only) + `volunteer-list.blade.php` (mirrors `school-list.blade.php`: search, status badge, single Edit button) + `app/Livewire/Setting/Volunteer.php` modal (`saveVolunteer()`: validate name/phone/email/notes/status with `Rule::enum(VolunteerStatus::class)`, create-or-update, dispatch `volunteer-changed`+`MessageChanged`) + `volunteer.blade.php`.

**2. Activity Type admin CRUD** — `app/Livewire/Setting/ActivityTypeList.php` (byte-for-byte copy of `GradeList`'s shape: `getActivityTypeListProperty()`, `removeActivityType($id)` guarded by `->activities()->exists()`) + `activity-type-list.blade.php` (copy of `grade-list.blade.php`) + `app/Livewire/Setting/ActivityType.php` modal (`saveActivityType()`: validate name unique via `Rule::unique('activity_types','name')->ignore($id)`, category nullable `Rule::enum(ActivityCategory::class)`) + `activity-type.blade.php`. Both #1 and #2 wired into `resources/views/livewire/setting/index.blade.php` as a new `grid-cols-1 lg:grid-cols-2` row after the existing School/Grade row.

**3. Volunteer check-in/check-out** — `app/Livewire/Attendance/AttendanceVolunteer.php` (`#[Title('Volunteers')]`, structural copy of `AttendanceStudent`: `checkIn`/`checkOut` with the identical double-checkin guard, `render()` filters `->active()` + optional `currentlyIn`) + `attendance-volunteer.blade.php` (copy of `attendance-student.blade.php`, columns Name/Phone/Email/Notes/Status/Total stay/Actions; Actions shows "Check out" + "Log Activity" side by side when checked in, "Check in" otherwise). Route: `Route::get('/volunteers', AttendanceVolunteer::class)->name('volunteers');` in `routes/web.php` (same auth group as `/attendance`). Nav: new `$navLinks` entry in `app.blade.php` after Attendance.

**4. Activity logging modal** — `app/Livewire/Attendance/LogVolunteerActivity.php` (`ModalComponent`, `mount(Volunteer $volunteer)`, `#[Computed] activityTypes()`/`eligibleStudents()`, `logActivity()` re-checks the volunteer is still checked in at submit time — defends against a race with another tab's checkout — then creates the `VolunteerActivity` and `->students()->attach($studentIds)`) + `log-volunteer-activity.blade.php` (activity type select, multi-select students, notes textarea). Opened from the "Log Activity" button in #3.

**5. Dashboard widget — explicitly skipped.** The PRD never states a dashboard requirement for volunteers; not inventing one. Easy to add later (`VolunteerAttendance::where('current_in', true)->count()`) if wanted.

**6. Report tab: Volunteer Activity report** — `app/Livewire/Report/VolunteerReport.php` (date range defaulted to current month, optional `volunteerId` filter, `render()` builds `hoursByVolunteer` from `VolunteerAttendance`, `activityCountsByType` from `VolunteerActivity`, plus a raw `activityLog` when a volunteer is selected) + `volunteer-report.blade.php` (mirrors `alumni-report.blade.php`: filter form, 3 stat cards, hours-by-volunteer table, activities-by-type table, conditional activity-log table). Wired into `resources/views/livewire/report/index.blade.php` as tab #7.

**7. Student Detail: read-only Volunteer Activities card** — one-line change to `StudentDetail::loadStudent()` adding `'volunteerActivities' => fn ($q) => $q->orderByDesc('date')`, `'volunteerActivities.activityType'`, `'volunteerActivities.volunteer'` to the eager-load array. New full-width card in `student-detail.blade.php` below the existing two 2-column rows (both already full), mirroring the Guardian Information card shape minus the add-button/actions column: Date/Activity Type/Category/Volunteer/Students/Notes.

**8. Seeder** — `database/seeders/ActivityTypeSeeder.php` + one line in `DatabaseSeeder::run()`.

## Tests

Mirror the exact existing test files for each piece: `GradeSettingTest.php` → `VolunteerSettingTest.php` (no delete-guard test, since Volunteer has no delete UI) and `ActivityTypeSettingTest.php` (full delete-guard test, since ActivityType does); `AttendanceStudentTest.php` → `AttendanceVolunteerTest.php` (including the double-checkin-guard test and an inactive-volunteer-hidden-from-search test mirroring the graduated-student test); new `LogVolunteerActivityTest.php` (creates activity tied to open visit, attaches students, allows zero students, requires activity type, fails gracefully if not checked in); `AlumniReportTest.php` → `VolunteerReportTest.php` (critically: a test asserting hours come from `total_time` regardless of how many activities exist on that visit — this is the regression guard for the "no duration on VolunteerActivity" gap); extend `tests/Feature/StudentDetailTest.php` with one new test for the activity history card, and re-verify (not assume) the existing `queryCount < 15` N+1 guard still holds after the new eager load.

## Verification

1. `docker exec kepepeo-laravel.test-1 php artisan db:seed --class=ActivityTypeSeeder` — confirm the 3 types seed cleanly.
2. `docker exec kepepeo-laravel.test-1 php artisan test` — full suite green, including all new test files above.
3. Live check via curl/tinker (as done throughout this project): log in, hit `/settings` and confirm Volunteers/Activity Types cards render and CRUD works; hit `/volunteers`, check a volunteer in, confirm "Log Activity" appears, log an activity with students attached, check out, confirm total_time accumulated; hit `/report` and confirm the new tab renders with correct hours/counts; hit a student's `/student-detail/{id}` who received a logged activity and confirm the new card shows it.
4. `npm run build` after new Blade files land (Tailwind class scan).
