# School module v1 — add a School roster/detail page

## Context

Finishing the "each entity as a module" audit. A research pass on the four remaining Settings entities (School, Grade, Activity Type, Job Title) found that three are already complete as-is: Grade's promotion chain and per-grade counts are already visualized (in `grade-list.blade.php` and `GradeDistributionReport`), Activity Type's only interesting aggregate (activity counts) already lives in `VolunteerReport`, and Job Title is a genuinely flat label with no other data. The one real, if minor, gap: **School has a real relation (`School::students()`, a `hasManyThrough(Student, SchoolStudent)`) and nowhere in the app shows a roster of who attends a given school** — only an aggregate per-school count on the dashboard (`AttendingStudentsBySchool`). This plan closes that one gap.

## Scope decision

School only has one field (`name`), already editable via the existing `Setting\School` modal (`app/Livewire/Setting/School.php`, opened from `SchoolList`). Unlike Student/Volunteer/Book, there's no multi-field "Basic Information" worth duplicating onto a new page — that would just be redundant with the modal that already works correctly. So this detail page is **read-only**: a roster, not an edit form. Renaming stays on the existing modal; the detail page's header gets an "Edit" button that opens that same modal (`setting.school`), reusing it rather than duplicating single-field validation logic.

Access stays admin-only, matching School's current boundary — unlike Student/Volunteer/Book (which have non-admin-facing operational pages separate from admin-gated Settings), School has no non-admin-facing page at all today; its only existing route (`/settings/schools`) sits inside `Route::middleware(['admin'])->group(...)` (`routes/web.php:47-61`). The new detail route goes in the same group.

## Implementation

**Route** (`routes/web.php`, inside the existing `admin` middleware group): `Route::get('/school-detail/{school_id}', SchoolDetail::class)->name('school-detail');` — naming matches the `/student-detail`, `/volunteer-detail`, `/book-detail` convention even though this one happens to be admin-gated.

**`app/Livewire/School/SchoolDetail.php`** (new namespace, mirrors `App\Livewire\Student`/`Volunteer`/`Book`):
- `mount()`: load the `School` by route param, `abort(404)` if missing (mirrors `StudentDetail`/`VolunteerDetail`).
- Roster query: **not** `School::students()` (a plain `hasManyThrough` with no access to the `SchoolStudent` pivot's `is_current` flag) — instead query `SchoolStudent::where('school_id', $id)->with(['student', 'student.grades' => fn ($q) => $q->where('is_current', true)->with('gradeTable')])->get()`, so each row can show whether that particular school membership is current and the student's current grade. Sort current-first, then by student name.
- `render()`: re-loads fresh (same pattern as the other detail pages), returns the view titled with the school's name.

**`resources/views/livewire/school/school-detail.blade.php`** (new):
- Header: back link to `route('settings-schools')` (the natural "list" this page belongs under, matching how Volunteer's detail page backs to `route('volunteers')`), school name as `h2`, a student-count badge, and an "Edit" button that dispatches `openModal` for `setting.school` with `schoolId` — same call shape already used in `school-list.blade.php:57`.
- One card: "Students" roster table — Name, Grade (current, or "—"), Status (Current/Past badge — reuse the same green "Current" pill classes already used for schools/grades on `student-detail.blade.php`), empty state "No students recorded for this school yet."
- Add a `#[On('school-changed')]` no-op refresh listener so the header updates if the name is changed via the modal without a full page reload.

**Wiring in `resources/views/livewire/setting/school-list.blade.php`**: add a "View details" eye-icon link (same SVG used for Student/Volunteer's fix) next to the existing Edit button, linking to `route('school-detail', $school->id)`.

## Tests

New `tests/Feature/SchoolDetailTest.php`, mirroring `StudentDetailTest.php`'s shape:
- Page shows the requested school's name and its students, not an unrelated school's.
- A student whose membership at this school is not current shows a "Past" badge, not "Current".
- Requesting a non-existent school 404s.
- Non-admin gets forbidden (this route sits in the admin group — mirror the existing admin-route-list assertion already in `tests/Feature/AuthorizationTest.php:12-33`, adding `/school-detail/{id}` to that path array the same way the Settings sub-pages were added previously).

## Verification

- `php artisan test --filter=SchoolDetail` plus the `AuthorizationTest` update, then the full suite (currently 269 passing).
- `npm run build`.
- Manual browser check flagged as usual: the roster page and its Edit-button-opens-modal flow, light/dark.
