# PRD: Volunteer Management

**Status:** Draft
**Owner:** TBD
**Last updated:** 2026-07-07

## 1. Problem

Kipepeo tracks students, schools, grades, attendance, and the library — but has no way to record who volunteers at the center, when they're on-site, or what they actually do while they're there (tutoring, extracurricular coaching, mentorship). Staff currently have no system of record for volunteer contributions, which makes it hard to:

- Know who's on-site at any given time.
- See what activities a given student has received (e.g., "has this student had a tutor this month?").
- Report volunteer hours and activity counts to funders/leadership.
- Recognize or follow up with active volunteers.

## 2. Goals

- Staff can register a volunteer once and reuse that record every time they come in — no re-entry per visit.
- Staff can check a volunteer in/out, mirroring the check-in/check-out pattern already used for [student attendance](../app/Livewire/Attendance/AttendanceStudent.php).
- Staff can log what a volunteer did during a visit — activity type, and optionally which student(s) or group was involved.
- Activity types are configurable (not hardcoded) so the center can add new offerings without an engineering request.
- Basic reporting: hours per volunteer, activity counts by type, and activity history per student.

## 3. Non-goals (out of scope for this PRD)

- **Volunteer vetting/safeguarding** (background checks, reference checks, supervision sign-off). Explicitly out of scope per stakeholder decision — assumed to be handled by an existing offline/HR process before a volunteer is entered into the system at all.
- Volunteer self-service login/portal. This PRD assumes staff operate the system on the volunteer's behalf, the same way students don't log in themselves today.
- Scheduling/booking volunteers in advance (this PRD covers logging what happened, not planning what will happen).
- Payments, stipends, or expense tracking for volunteers.

These may be worth revisiting later, but are called out here so they're deliberately deferred, not silently missed.

## 4. Users & permissions

Following the existing role model (`App\Enums\UserRole`: `admin` / `user`) and the precedent set by [routes/web.php](../routes/web.php):

| Action | Who |
|---|---|
| Check a volunteer in/out | Any authenticated staff member (`user` or `admin`) — matches today's `/attendance` access |
| Log an activity for a checked-in volunteer | Any authenticated staff member |
| Create/edit/deactivate a volunteer record | Admin only — matches how `/settings` (schools, grades, job titles) is admin-gated today |
| Manage the list of activity types | Admin only, under Settings — same pattern as Grade/School management |
| View volunteer reports | Any authenticated staff member — matches today's `/report` access (see §8) |

## 5. Proposed data model

Mirrors existing conventions (`Student`/`Attendance`/`Grade` patterns) rather than introducing a new style.

**`volunteers`** (new — parallel to `students`)
- `id`
- `name`
- `phone`, `email` (nullable)
- `notes` (nullable, free text — e.g. availability, specialty)
- `status`: `active` / `inactive` (soft-disable without deleting history)
- `deleted_at` (soft deletes, consistent with the fix already applied to `students`/`schools`/`grades`)
- timestamps

**`activity_types`** (new — parallel to `grades`, a simple admin-managed lookup)
- `id`
- `name` (e.g. "Tutoring", "Chess Club", "STEM Club", "Basic IT Training", "Mentorship")
- `category` (optional grouping: `tutoring` / `extracurricular` / `mentorship` / `other` — lets the UI group the initial three request types while still allowing free-form additions)
- `deleted_at`, timestamps

**`volunteer_attendances`** (new — structurally identical to `attendances`)
- `id`
- `volunteer_id`
- `date`
- `current_in` (boolean)
- `total_time` (integer, seconds — matches `attendances.total_time`)
- timestamps

**`volunteer_attendance_attrs`** (new — structurally identical to `attendance_attrs`, supports multiple check-in/out cycles per day)
- `id`
- `volunteer_attendance_id`
- `volunteer_id`
- `date`, `time_in`, `time_out` (nullable)
- timestamps

**`volunteer_activities`** (new — the actual activity log)
- `id`
- `volunteer_attendance_id` (FK — which check-in visit this activity happened during; see §8 on why this isn't just `volunteer_id` + `date`)
- `volunteer_id` (denormalized from the attendance record, for simpler queries — matches how `attendance_attrs.student_id` is already denormalized alongside `attendance_id`)
- `activity_type_id`
- `date` (denormalized, same rationale)
- `notes` (nullable — free text on what was covered)
- timestamps

**`volunteer_activity_student`** (new — pivot; an activity can involve zero, one, or many students)
- `volunteer_activity_id`
- `student_id`

Zero students on an activity covers group/general sessions (e.g. "ran a STEM club drop-in session, no fixed roster") without forcing a student to be picked.

## 6. User stories

1. As **staff**, when a volunteer arrives, I can check them in from a list (reusing the same UI pattern as student attendance), and check them out when they leave.
2. As **staff**, while a volunteer is checked in (or as part of checking them out), I can log one or more activities they did — activity type, optional linked student(s), optional notes.
3. As **staff**, I can look up a student's detail page and see a history of volunteer-provided activities they've received (tutoring sessions, mentorship, etc.) — extends the existing student detail page.
4. As an **admin**, I can add/edit/deactivate volunteer records, the same way I manage schools and grades today.
5. As an **admin**, I can manage the list of activity types (add "Coding Club" next term without needing a code change).
6. As **staff/admin**, I can see a report of volunteer hours and activity counts over a date range (extends the existing `/report` module).

## 7. Functional requirements

- **Volunteer roster** (admin): create, edit, deactivate. Deactivated volunteers stay visible in history but can't be checked in.
- **Check-in/out** (staff): same UX as `/attendance` today — search/filter list, one-click check in, one-click check out, prevents double check-in (mirrors the existing `"Student already checked in"` guard in `AttendanceStudent::checkIn()`).
- **Activity logging** (staff): form to record activity type + optional student(s) + notes, tied to a volunteer's visit.
- **Activity type management** (admin): simple CRUD under Settings, same pattern as `Setting/GradeList.php`/`Setting/SchoolList.php` (including the "can't delete if in use" guard those already have).
- **Reporting**: volunteer hours by date range, activity counts by type, activity history per student (surfaced on the student detail page) and per volunteer.

## 8. Resolved decisions

Previously open questions, decided so the PRD can move forward. Revisit any of these if they turn out wrong once real usage starts.

- **Reporting visibility → open to all staff.** Matches the existing `/report` module, which isn't admin-gated today. Coordinating volunteer activity is a shared operational concern, not sensitive data like user/role management — no reason to restrict it.

- **Multiple activities per visit → yes, allowed.** A volunteer might tutor for 30 minutes then help run STEM club for another 30 — that's one visit, two activities. This is why §5's `volunteer_activities` table links to a specific `volunteer_attendance_id` (one visit) rather than just `volunteer_id` + `date`: it lets several activities hang off one check-in, and correctly separates them if the same volunteer checks in again later the same day.

- **Recurring vs. drop-in volunteers → no dedicated field for MVP, use `notes`.** Scheduling/booking is already a non-goal (§3), and there's no stated need yet for capacity planning or shift assignment. Adding a `notes` field on `volunteers` (already in the schema) covers "usually comes Tuesdays" without building out a real scheduling system. If the org later wants actual shift scheduling, that's a bigger feature worth its own PRD, not a bolt-on here.

- **Data retention → same lifecycle as everything else: soft-deleted, kept indefinitely.** No stated retention policy, so default to consistency with `students`/`schools`/`grades` (all soft-deleted, kept indefinitely). If the org has a legal/compliance obligation around volunteer PII (e.g., "delete after N years of inactivity"), that's an organizational policy decision to make explicitly, not something to assume — flagging it here rather than guessing.

## 9. Suggested phasing

- **MVP**: volunteer roster (admin CRUD) + check-in/check-out + activity logging with the three known activity types seeded (Tutoring, Extracurricular Training, Mentorship), sub-typed via `activity_types.category`. Reporting open to all staff per §8.
- **V2**: admin-managed activity type list, per-student activity history on the student detail page, fuller reporting (hours by volunteer, activity counts by type/date range).
- **Later**: anything explicitly deferred in §3 (vetting/safeguarding, self-service login, scheduling, payments) if priorities change.
