# Volunteer module v1 — add a Volunteer Detail page

## Context

Continuing the "each entity as a module" work (Student was brought up to par first — see `docs/student-module-implementation-plan.md`). The user chose Volunteer next. The biggest gap found earlier: **Volunteer has no detail page at all.** Today, everything about a volunteer is scattered:

- Editing basic info (`name`/`phone`/`email`/`notes`/`status`/`hourly_rate`) only happens via a small modal opened from the admin-only Settings list (`App\Livewire\Setting\Volunteer`, `resources/views/livewire/setting/volunteer-list.blade.php`).
- Check-in/out and "Log Activity" happen on the `/volunteers` roster (`App\Livewire\Attendance\AttendanceVolunteer`).
- Attendance hours and activity history only exist in aggregate/filtered form on the Volunteer Activity report tab (`App\Livewire\Report\VolunteerReport`).

There is no single page to view one volunteer's full picture, the way `/student-detail/{id}` already does for Student. This plan adds that page, mirroring Student's detail-page structure and card conventions exactly (data source: relations already exist — `Volunteer::attendances()`, `Volunteer::activities()`, `VolunteerAttendance::attrs()`, `VolunteerActivity::activityType()`/`students()` — no new relations needed, unlike the Student plan which needed to add `Student::rentals()`).

## Design decisions

- **Namespace**: new `App\Livewire\Volunteer\VolunteerDetail`, mirroring `App\Livewire\Student\StudentDetail` (Student already established "one namespace per entity" as the convention worth repeating).
- **Route**: `Route::get('/volunteer-detail/{volunteer_id}', VolunteerDetail::class)->name('volunteer-detail');` in the same general `auth:sanctum` group as `/student-detail` and `/volunteers` (routes/web.php:37-39) — not admin-gated, matching `/volunteers` today.
- **Basic Information is an inline edit form on the page**, not a modal — mirrors both `StudentDetail` (name/gender/dob form) and `BookDetail` (title/author form), which both edit in place rather than reusing a separate modal component. Validation rules are copied from `App\Livewire\Setting\Volunteer::saveVolunteer()` (`app/Livewire/Setting/Volunteer.php:47-54`) as plain component properties — no Form object, since `Setting\Volunteer` (the thing being mirrored here) doesn't use one either (unlike Student, which does use `UpdateStudentForm`). On save, dispatch the existing `volunteer-changed` event (already used by `AttendanceVolunteer`'s listener and the Settings modal) plus a `MessageChanged`-style flash, consistent with the rest of the app.
- **No check-in/out or Log Activity actions on this page** — those stay on `/volunteers`, matching how `StudentDetail` doesn't duplicate `/attendance`'s check-in/out either. This page is for viewing history + editing profile fields, not for daily operations.
- **Scope stays view + edit, no delete/deactivate action** — deactivating a volunteer is just a normal `status` field edit via the Basic Information form (no separate destructive action like Student's "Graduate").

## Page sections (mirrors `student-detail.blade.php`'s card layout)

Four cards in two `grid-cols-1 lg:grid-cols-2` rows — Row 1: Basic Information + Earnings Summary (the "profile" row); Row 2: Attendance History + Activity Log (the "history" row). Same `bg-white rounded-lg shadow dark:bg-gray-800` card shell, `h3` headers, table `thead`/`tbody`/empty-state pattern used everywhere else in this codebase.

1. **Basic Information** card — inline form: Name, Phone, Email, Notes, Status (select, `VolunteerStatus::cases()`), Hourly Rate. Same field/validation set as `Setting\Volunteer`.

2. **Earnings Summary** card (per clarification: estimated earnings only, no payment ledger for v1) — a small `fromDate`/`toDate` filter (defaults to the current month, same default as `VolunteerReport::mount()`) showing: Days Present, Total Hours, Hourly Rate, Estimated Earnings for that range. Computed exactly like `VolunteerReport`'s `hoursByVolunteer` row (`app/Livewire/Report/VolunteerReport.php:53-67`): sum `total_time` from `attendances` in range, `estStipend = totalSeconds / 3600 * hourly_rate` when a rate is set, else show "No hourly rate set" instead of an amount. **Currency**: format as `KSH ` + `number_format($amount, 2)` (e.g. `KSH 1,250.00`) — apply this same `KSH` prefix to `VolunteerReport`'s existing "Est. Stipend" column too (`resources/views/livewire/report/volunteer-report.blade.php:98`, currently unlabeled `number_format(...)`), so the two places showing stipend amounts are consistent.

3. **Attendance History** card — Date, Time In/Out (per `attrs` row, "Still in" if `time_out` null), Total Time (`$volunteer->secondsToHms(...)`, already on the model). Capped at the most recent 15, with a note linking to `/report` when capped — exact pattern from `student-detail.blade.php`'s new Attendance History card.

4. **Activity Log** card — Date, Activity Type, Students (comma-joined names), Notes — mirrors both `StudentDetail`'s read-only "Volunteer Activities" card and `volunteer-report.blade.php`'s "Activity Log" table. Capped at the most recent 20.

## Component implementation

**`app/Livewire/Volunteer/VolunteerDetail.php`**
- `mount()`: load volunteer by `request()->route('volunteer_id')`, `abort(404)` if missing (mirrors `StudentDetail::mount()`), populate form properties from it; also set `$earningsFromDate`/`$earningsToDate` to the current month (`now()->startOfMonth()`/`now()`) and call `calculateEarnings()`.
- `loadVolunteer($id)`: eager-loads `'attendances' => fn ($q) => $q->orderByDesc('date')->limit(15)`, `'attendances.attrs'`, `'activities' => fn ($q) => $q->orderByDesc('date')->limit(20)`, `'activities.activityType'`, `'activities.students'`. (Full, uncapped attendance rows for the earnings range are queried separately in `calculateEarnings()`, not from this capped eager-load.)
- `calculateEarnings()`: validates the date range (`toDate >= fromDate`), queries `VolunteerAttendance::where('volunteer_id', ...)->whereBetween('date', [...])`, sums `total_time`, counts rows as days present, computes `estStipend` the same way as `VolunteerReport` (see above). Called on mount and whenever the range filter changes.
- `update()`: validates (same rules as `Setting\Volunteer::saveVolunteer()`), saves, flashes success, dispatches `volunteer-changed`.
- `render()`: re-loads fresh (mirrors `StudentDetail::render()`'s pattern of reloading on every request rather than relying on stale mounted state), returns the view titled with the volunteer's name.

**`resources/views/livewire/volunteer/volunteer-detail.blade.php`** — new file, built from the four cards above plus the session-flash banner + `p-2 md:p-6` + `h2` + back-link header, exactly matching `student-detail.blade.php`'s top-of-page structure (back link goes to `route('volunteers')`, the roster page, since that's the natural "list" this detail page belongs under).

## Wiring in the two existing list/roster pages

- **`resources/views/livewire/attendance/attendance-volunteer.blade.php`**: add a "View details" eye-icon link (same SVG just used to fix `student-list.blade.php`'s icon) as the first action in the row's action group, before Log Activity/Check-in/Check-out, linking to `route('volunteer-detail', $volunteer->id)`.
- **`resources/views/livewire/setting/volunteer-list.blade.php`**: add the same eye-icon "View details" link next to the existing Edit button.

## Tests

New `tests/Feature/VolunteerDetailTest.php`, mirroring `tests/Feature/StudentDetailTest.php`'s shape and helper-function style:
- Page shows the requested volunteer's name, not an unrelated one.
- No N+1 scaling per related row (same `DB::listen` counting pattern).
- Basic Information form successfully updates and dispatches `volunteer-changed`.
- Attendance History card shows a created `VolunteerAttendance` + `VolunteerAttendanceAttr` row with formatted date/duration.
- Activity Log card shows a created `VolunteerActivity` (with `activityType` and attached `students`) — mirrors the existing `student detail page shows volunteer activity history` test but from the volunteer's side.
- Earnings Summary shows the correct hours/days-present count and a `KSH`-prefixed estimated amount when `hourly_rate` is set, and "No hourly rate set" (no amount) when it isn't — mirrors `VolunteerReportTest.php`'s rated-vs-unrated assertions.
- Requesting a non-existent volunteer id 404s.

No change needed to `tests/Feature/AuthorizationTest.php` — the new route is in the general auth group, not admin-only, same as `/volunteers` and `/student-detail`.

## Verification

- `php artisan test --filter=VolunteerDetail` for the targeted new page, then the full `php artisan test` suite (currently 254 passing) to confirm no regressions.
- `npm run build` to confirm no Blade/Tailwind issues.
- Manual browser check flagged as usual (no browser tooling): the new page in both light/dark mode, the two new "View details" links, and that editing Basic Information there doesn't desync from the Settings modal (both write to the same `Volunteer` row).
