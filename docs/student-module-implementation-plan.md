# Student module v1 — close the attendance-visibility gap

## Context

The user wants each entity in the app treated as a "module" — a consistent, complete set of pages/features — and asked to start with Student. A research pass compared Student against Volunteer and Book (the app's other two "rich" entities):

- Student already has a solid, complete set: a list page, a detail page (edit info, guardians, schools, grades, graduate action, volunteer-activity log), and a create modal. This is already ahead of Volunteer, which has no detail page at all.
- The clear, concrete gaps, all on the same theme — **Student's own history is invisible on its own detail page**, even though the data already exists via relations used elsewhere:
  1. The Student Detail page (`resources/views/livewire/student/student-detail.blade.php`) shows Guardians, Schools, Grades, and Volunteer Activities as history tables — but not attendance, despite attendance being the single most core fact about a student in this app.
  2. It also has no book-rental history, even though Book already tracks exactly this via `Rental` (`checkedOutTo` → Student) and already has a full "Books on Loan" list UI (`BookOnRent`/`book-on-rent.blade.php`) with status badges and a Return action — that data and UI pattern just isn't surfaced per-student.
  3. In the Reports tab (`/report`), the "Attendance Analytics" tab (`AttendanceReport`) shows aggregate stats (by gender/school/grade, daily totals with charts) but has no per-student breakdown — there's no way to see "how many days did this specific student attend, and for how long" anywhere in the app. Volunteer has exactly this via `VolunteerReport`'s "Hours by Volunteer" table + a `volunteerId` filter that reveals a day-by-day activity log for one person. Student has no equivalent.

This plan closes all three gaps using patterns already established elsewhere in the codebase — no new architecture, just filling in the places where Student falls short of its siblings. (Side note, not part of this plan: `BookOnRent` has no route or nav link today — it's built but unreachable. Worth a future fix, out of scope here.)

## Part 1 — Attendance History section on the Student Detail page

**`app/Livewire/Student/StudentDetail.php`**
- In `loadStudent()`, add `'attendances' => fn ($query) => $query->orderByDesc('date')->limit(15)` and `'attendances.attrs'` to the eager-load list (alongside the existing `guardians`, `schools.school`, `grades.gradeTable`, `volunteerActivities`).
- Reuse `Student::secondsToHms()` (already defined on the model — `$studentDetails->secondsToHms(...)` in the blade) instead of duplicating a helper on the component.

**`resources/views/livewire/student/student-detail.blade.php`**
- Added a new "Attendance History" card, same shape as the existing Guardian/School/Grade/Volunteer Activities cards. Columns: Date, Time In / Time Out (each `attrs` row's `time_in`–`time_out` pair, or "Still in" if `time_out` is null), Total Time (via `secondsToHms`).
- Capped to the most recent 15 records, with a note + link to `/report` (Attendance Analytics) when the cap is hit.

**Tests**: extend `tests/Feature/StudentDetailTest.php` with a case creating an `Attendance` + `AttendanceAttr` row and asserting the rendered page contains the date and formatted duration.

## Part 2 — Book Rental History section on the Student Detail page

**`app/Models/Student.php`**
- Added `public function rentals(): HasMany { return $this->hasMany(Rental::class); }` — mirrors `Book::rentals()`, just the inverse side. `Rental` already has `checkedOutTo()` (belongsTo Student).

**`app/Livewire/Student/StudentDetail.php`**
- In `loadStudent()`, added `'rentals' => fn ($query) => $query->orderByDesc('rented_at')->limit(20)` and `'rentals.book'` to the eager-load list.
- Added `#[On('rental-changed')]` no-op refresh listener (same shape as the existing `student-changed` listener) so the rentals table re-renders after a Return action.

**`resources/views/livewire/student/student-detail.blade.php`**
- Added a "Book Rentals" card next to Attendance History (both in a new `grid-cols-1 lg:grid-cols-2` row, above the existing full-width Volunteer Activities row).
- Columns and status-badge logic copied directly from `resources/views/livewire/book/book-on-rent.blade.php` (Title, Due Date, Status — Returned/Overdue/Borrowed with the same color classes).
- Reuses the existing Return action as-is: `wire:click="$dispatch('openModal', { component: 'book.return-book', arguments: { rentalId: ... }})"` for any rental without `returned_at` — no new modal component, `App\Livewire\Book\ReturnBook` already exists and already dispatches `rental-changed` on success.
- Capped to the most recent 20 rentals — no "view all" link, since there's no reachable full-history page today (`BookOnRent` has no route).

**Tests**: extend `tests/Feature/StudentDetailTest.php` with a case creating a `Rental` for the student and asserting the page shows the book title and a status badge, plus a case confirming the Return button dispatches `openModal` with the right `rentalId`.

## Part 3 — Per-student hours table + drill-down log in Attendance Analytics

Mirrors `App\Livewire\Report\VolunteerReport`'s existing `hoursByVolunteer` / `volunteerId` / `activityLog` pattern exactly, applied to `AttendanceReport`.

**`app/Livewire/Report/AttendanceReport.php`**
- Add `public $studentId = ''` (validated `nullable|exists:students,id`, same as `VolunteerReport::$volunteerId`).
- In `filter()`, after the existing aggregate computations, add:
  - `$hoursByStudent`: group the already-fetched `$attendances` by `student_id`, producing `name`, `visits` (days present), `totalSeconds`, sorted desc by `totalSeconds` — same shape as `VolunteerReport::hoursByVolunteer()`.
  - `$attendanceLog`: when `$studentId` is set, the individual `attrs`-level rows (date, time_in, time_out) for that student in range, sorted by date desc — same shape as `VolunteerReport::activityLog`.
- Pass both to the view, plus a `students` list (`Student::active()->orderBy('name')->get()`) for the filter dropdown.

**`resources/views/livewire/report/attendance-report.blade.php`**
- Add a "Student" `<select>` to the filter form (mirrors `volunteer-report.blade.php`'s volunteer select), between the date fields and the buttons.
- Add a new "Hours by Student" table section (mirrors `volunteer-report.blade.php`'s "Hours by Volunteer" table), placed after the existing "Daily Breakdown" table.
- Add a conditional "Attendance Log" table section shown only `@if ($studentId)` (mirrors `volunteer-report.blade.php`'s "Activity Log"), listing date / time in / time out / duration for that one student.

**Tests**: extend `tests/Feature/Livewire/Report/AttendanceReportTest.php` with cases for `hoursByStudent` totals and the `studentId`-filtered log, mirroring the equivalent assertions in `tests/Feature/Livewire/Report/VolunteerReportTest.php`.

## Verification

- `php artisan test --filter=StudentDetail` and `--filter=AttendanceReport` for the targeted areas, then the full `php artisan test` suite to confirm no regressions.
- `npm run build` to confirm no Blade/Tailwind class issues in the new markup.
- Flag for manual browser check (no browser tooling available): the new Attendance History and Book Rentals cards on a student with real data, and the new Hours-by-Student table + drill-down log on the Attendance Analytics report tab, in both light and dark mode.

## After this

Once Student is at parity, the same "what does this entity's page show vs. what data actually exists on the model" audit can be repeated for Volunteer (e.g. a detail page) and Book next — but that's future work, not part of this pass.
