<div class="p-2 md:p-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-2xl font-semibold text-gray-700 dark:text-white">User Manual</h2>
        <button type="button" onclick="window.print()"
            class="no-print inline-flex items-center p-2 px-4 bg-teal-600 hover:bg-teal-700 text-white rounded-lg text-sm">
            <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z" />
            </svg>
            Print
        </button>
    </div>

    <div class="printable lg:grid lg:grid-cols-4 lg:gap-6 lg:items-start">
        {{-- Table of contents --}}
        <nav aria-label="Manual sections"
            class="no-print mb-6 lg:mb-0 lg:sticky lg:top-6 bg-white dark:bg-gray-800 rounded-lg shadow p-4 text-sm">
            <p class="font-semibold text-gray-900 dark:text-white mb-2">On this page</p>
            <ul class="space-y-1 text-gray-600 dark:text-gray-300">
                <li><a href="#getting-started" class="hover:text-primary-700 dark:hover:text-primary-400">Getting started</a></li>
                <li><a href="#dashboard" class="hover:text-primary-700 dark:hover:text-primary-400">Dashboard</a></li>
                <li><a href="#attendance-students" class="hover:text-primary-700 dark:hover:text-primary-400">Attendance &ndash; students</a></li>
                <li><a href="#attendance-volunteers" class="hover:text-primary-700 dark:hover:text-primary-400">Attendance &ndash; volunteers</a></li>
                <li><a href="#students" class="hover:text-primary-700 dark:hover:text-primary-400">Students</a></li>
                <li><a href="#volunteers" class="hover:text-primary-700 dark:hover:text-primary-400">Volunteers</a></li>
                <li><a href="#books" class="hover:text-primary-700 dark:hover:text-primary-400">Books &amp; rentals</a></li>
                <li><a href="#data-entry" class="hover:text-primary-700 dark:hover:text-primary-400">Data entry</a></li>
                <li><a href="#reports" class="hover:text-primary-700 dark:hover:text-primary-400">Reports</a></li>
                @if (Auth::user()->isAdmin())
                    <li class="pt-2 mt-2 border-t border-gray-100 dark:border-gray-700 text-xs font-semibold uppercase tracking-wide text-gray-400">Admin only</li>
                    <li><a href="#settings" class="hover:text-primary-700 dark:hover:text-primary-400">Settings</a></li>
                    <li><a href="#users" class="hover:text-primary-700 dark:hover:text-primary-400">Users</a></li>
                    <li><a href="#backup-restore" class="hover:text-primary-700 dark:hover:text-primary-400">Backup &amp; restore</a></li>
                @endif
                <li class="pt-2 mt-2 border-t border-gray-100 dark:border-gray-700"><a href="#roles" class="hover:text-primary-700 dark:hover:text-primary-400">Roles &amp; permissions</a></li>
                <li><a href="#faq" class="hover:text-primary-700 dark:hover:text-primary-400">Troubleshooting &amp; FAQ</a></li>
            </ul>
        </nav>

        {{-- Content --}}
        <div class="lg:col-span-3 prose prose-sm sm:prose-base dark:prose-invert max-w-none
            prose-h2:mt-0 prose-h2:mb-3 prose-h3:mb-2 prose-headings:text-gray-900 dark:prose-headings:text-white
            prose-a:text-primary-700 dark:prose-a:text-primary-400 space-y-8">

            <section id="getting-started" class="scroll-mt-4 bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-6">
                <h2>Getting started</h2>
                <p>
                    Kipepeo is the day-to-day tool for running the space: who checked in today, who's borrowed a
                    book and hasn't returned it, and the records behind the reports the organization needs.
                </p>
                <ul>
                    <li>An admin creates your account under <strong>Users</strong> and gives you the password
                        directly &mdash; there's no self-signup.</li>
                    <li>The first time you log in with that password, you're required to set your own new one
                        before you can do anything else.</li>
                    <li>Every account has one of two roles &mdash; <strong>admin</strong> or <strong>user</strong>.
                        See <a href="#roles">Roles &amp; permissions</a> for exactly what each can do.</li>
                    <li>Manage your own name/password/two-factor authentication any time from the account menu
                        (bottom of the sidebar) &rarr; <strong>My Profile</strong>.</li>
                </ul>
            </section>

            <section id="dashboard" class="scroll-mt-4 bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-6">
                <h2>Dashboard</h2>
                <p>The Home page (sidebar &rarr; Home) is a live snapshot of today, plus quick actions and trend charts.</p>
                <ul>
                    <li><strong>Quick access</strong> &mdash; one-click shortcuts to mark attendance, check volunteers
                        in/out, add a student, or rent a book without leaving the dashboard.</li>
                    <li><strong>Currently in</strong> &mdash; every student on-site right now, searchable. A book
                        icon with a tooltip flags anyone with an overdue rental; a birthday cake icon marks a
                        birthday today.</li>
                    <li><strong>Birthdays this week</strong> and <strong>Volunteers</strong> summary widgets.</li>
                    <li><strong>Trends &amp; overview</strong> &mdash; attendance trend, student breakdown, book
                        inventory, and alumni charts, plus a same-day attendance-by-school table.</li>
                </ul>
                <p>
                    Checking a student in whose birthday is today sets off a short confetti celebration and a
                    banner, wherever you check them in from. Checking in a student with an overdue book shows a
                    reminder banner naming the book.
                </p>
            </section>

            <section id="attendance-students" class="scroll-mt-4 bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-6">
                <h2>Attendance &ndash; students</h2>
                <p>Sidebar &rarr; <strong>Attendance</strong> (<code>/attendance</code>) is the full daily roster.</p>
                <ul>
                    <li>Search by name, filter by <strong>In</strong> / <strong>Out</strong>, sort any column.</li>
                    <li>Each row shows today's grade, school, age, primary guardian, status, and total time
                        on-site so far, plus a <strong>Check in</strong>/<strong>Check out</strong> button.</li>
                    <li>A row already checked in today can't be checked in again &mdash; you'll see "Student
                        already checked in".</li>
                    <li>The clock icon opens that student's recent attendance history.</li>
                    <li>An amber book icon with a tooltip flags a student with an overdue rental.</li>
                </ul>
                <p>
                    For a faster in/out board without the full table (e.g. a volunteer staffing the door), use
                    the dashboard's <strong>Mark attendance</strong> quick-access button instead &mdash; same
                    check-in/out logic, a shorter searchable list.
                </p>
            </section>

            <section id="attendance-volunteers" class="scroll-mt-4 bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-6">
                <h2>Attendance &ndash; volunteers</h2>
                <p>Sidebar &rarr; <strong>Volunteers</strong> (<code>/volunteers</code>) is the volunteer equivalent of the attendance page.</p>
                <ul>
                    <li>Search, filter by currently-in, check in/out &mdash; same pattern as student attendance.</li>
                    <li>Checking out accumulates that session's time into the volunteer's total for the day.</li>
                    <li><strong>Log activity</strong>: while a volunteer is checked in, record what they did
                        &mdash; one or more activity types (required), optionally which students they worked with
                        (only students already checked in today are selectable), and a free-text note. You can't
                        log an activity for a volunteer who isn't currently checked in.</li>
                </ul>
            </section>

            <section id="students" class="scroll-mt-4 bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-6">
                <h2>Students</h2>
                <p>Sidebar &rarr; <strong>Students</strong> (<code>/students</code>) is the full roster, separate from daily attendance.</p>
                <ul>
                    <li><strong>Add a student</strong>: name, gender, date of birth, school, grade, and a primary
                        guardian's name and phone number are all required. Phone must be a valid Kenyan number.</li>
                    <li>Search by name, filter by school, sort, and (admins) bulk-select rows to delete.</li>
                    <li>Opening a student takes you to their <strong>detail page</strong>, where you can:
                        <ul>
                            <li>Edit their name, gender, and date of birth.</li>
                            <li>Add more guardians, mark one "primary", or (admin) remove one.</li>
                            <li>Add school/grade history rows and mark which is current &mdash; a student can
                                have more than one on record, but only one counts as current at a time.</li>
                            <li>See their full attendance log and book-rental history.</li>
                        </ul>
                    </li>
                    <li><strong>Graduate</strong> (admin only): marks a student as an alumnus and drops them off
                        the active roster and attendance search. Only available once they're in their <em>final</em>
                        grade &mdash; one with no "next grade" configured in Settings &rarr; Grades.</li>
                </ul>
            </section>

            <section id="volunteers" class="scroll-mt-4 bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-6">
                <h2>Volunteers (roster)</h2>
                <p>
                    Don't confuse this with the <a href="#attendance-volunteers">Attendance &ndash; volunteers</a>
                    check-in board. The <strong>volunteer roster</strong> itself is managed from a volunteer's
                    detail page (open them from the Volunteers attendance list) or from Settings &rarr; Volunteers
                    (admin).
                </p>
                <ul>
                    <li>Editable fields: name, phone, email, status, notes, and an optional hourly rate.</li>
                    <li>The detail page includes an <strong>earnings estimate</strong>: pick a date range and it
                        totals days present, hours worked, and (if an hourly rate is set) an estimated payment.</li>
                </ul>
            </section>

            <section id="books" class="scroll-mt-4 bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-6">
                <h2>Books &amp; rentals</h2>
                <p>Sidebar &rarr; <strong>Books</strong> (<code>/books</code>), with a <strong>Books on Loan</strong> view alongside it.</p>
                <ul>
                    <li><strong>Add/edit a book</strong>: title and author are required; publisher, class, and
                        category are optional. The category dropdown is populated from Settings &rarr;
                        Book Categories, not hardcoded &mdash; ask an admin to add one if it's missing.</li>
                    <li>Each book tracks a number of physical copies; a book's detail page lets you add/remove
                        copies and mark individual copies lost or stolen.</li>
                    <li><strong>Rent a book</strong>: only students checked in <em>today</em>, or alumni, can be
                        selected as the borrower. Pick a due date after today. Rental fails if there are no
                        available copies.</li>
                    <li><strong>Return a book</strong> from the Books on Loan view, with an optional comment.</li>
                    <li>Overdue rentals (past due date, not yet returned) are what drive the amber "overdue" icon
                        you'll see on the attendance lists and the Currently In dashboard widget.</li>
                </ul>
            </section>

            <section id="data-entry" class="scroll-mt-4 bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-6">
                <h2>Data entry</h2>
                <p>
                    Sidebar &rarr; <strong>Data Entry</strong> (<code>/data-entry</code>) is a stripped-down intake
                    screen for adding students and recording their attendance quickly, side by side, without the
                    full Students page's extra fields and options &mdash; useful at a busy sign-in table. It can
                    also be used to back-fill a specific time-in/time-out for a past date rather than a live
                    check-in.
                </p>
            </section>

            <section id="reports" class="scroll-mt-4 bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-6">
                <h2>Reports</h2>
                <p>Sidebar &rarr; <strong>Report</strong> (<code>/report</code>) is a tabbed page; every tab has a Print button.</p>
                <ul>
                    <li><strong>Enrollment Summary</strong> &mdash; per-school totals, by gender.</li>
                    <li><strong>Grade Distribution</strong> &mdash; enrollment counts per grade.</li>
                    <li><strong>Attendance Analytics</strong> &mdash; pick a date range for gender/school/grade/age
                        breakdowns, hours per student <em>and</em> per grade, a daily breakdown, and a dedicated
                        girls' attendance table ranked by consistency (days present &divide; weekdays in range),
                        with the five most consistent girls highlighted.</li>
                    <li><strong>Daily Attendance Roster</strong> &mdash; the full check-in/out log for one day.</li>
                    <li><strong>Book &amp; Rental Circulation</strong> &mdash; rentals over a date range: on-time vs.
                        late returns, top books, rentals by category, and live current/overdue counts.</li>
                    <li><strong>Alumni Report</strong> &mdash; graduated students, filterable by date and grade.</li>
                    <li><strong>Volunteer Activity</strong> &mdash; hours and estimated stipend per volunteer over
                        a date range, activity-type totals, and a per-volunteer activity log.</li>
                </ul>
            </section>

            @if (Auth::user()->isAdmin())
                <section id="settings" class="scroll-mt-4 bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-6">
                    <h2>Settings <span class="text-sm font-normal text-gray-400">(admin only)</span></h2>
                    <p>Sidebar &rarr; Admin &rarr; <strong>Settings</strong> (<code>/settings</code>) is the hub for everything that shapes the dropdowns and rules used elsewhere in the app.</p>
                    <ul>
                        <li><strong>Schools</strong>, <strong>Grades</strong>, <strong>Activity Types</strong>,
                            and <strong>Book Categories</strong> &mdash; simple add/edit/delete lists. A grade also
                            has an optional "next grade", which is what Promote Students uses; deleting any of
                            these is blocked while records still reference it.</li>
                        <li><strong>Job titles</strong> &mdash; used on user accounts; same delete protection.</li>
                        <li><strong>Volunteers</strong> &mdash; the same roster described under
                            <a href="#volunteers">Volunteers</a>, manageable from here too.</li>
                        <li><strong>Promote Students</strong> &mdash; run at year end. Pick one or more grades;
                            students in a grade with a "next grade" configured move up to it, students in a grade
                            with none configured are automatically graduated instead.</li>
                        <li><strong>Import Students</strong> / <strong>Import Books</strong> &mdash; bulk-add from
                            an XLSX spreadsheet. Students: Name, Gender, School, Grade columns, in that order, no
                            header processing beyond skipping the first row. Books: Title, Author, Publisher,
                            Class, Copies. Runs as a background job; you'll get a notification when it finishes.</li>
                    </ul>
                </section>

                <section id="users" class="scroll-mt-4 bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-6">
                    <h2>Users <span class="text-sm font-normal text-gray-400">(admin only)</span></h2>
                    <p>Sidebar &rarr; Admin &rarr; <strong>Users</strong> (<code>/users</code>).</p>
                    <ul>
                        <li><strong>Add a user</strong>: name, unique email, job title, and role are required, plus
                            a starting password you set and hand to them directly. They're forced to change it the
                            first time they log in.</li>
                        <li><strong>Edit a user</strong>: name, job title, and role only &mdash; not email or
                            password from this form.</li>
                        <li>An admin can force a password reset on any account from that account's page.</li>
                    </ul>
                </section>

                <section id="backup-restore" class="scroll-mt-4 bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-6">
                    <h2>Backup &amp; restore <span class="text-sm font-normal text-gray-400">(admin only)</span></h2>
                    <p>Settings &rarr; <strong>Backup</strong> (<code>/settings/backup</code>).</p>
                    <ul>
                        <li>One button downloads a <code>.zip</code> containing a full database dump and the
                            server's current <code>.env</code> configuration file. Store it somewhere safe &mdash;
                            it contains real credentials.</li>
                        <li>There's no restore button here on purpose. Restoring overwrites a server's database
                            and configuration, so it only works from a <em>fresh, empty</em> install: visit
                            <code>/restore</code> on the target server (no login needed while it has zero user
                            accounts) and upload a backup <code>.zip</code>. The moment any account exists on that
                            install, <code>/restore</code> stops working for everyone.</li>
                        <li>Restoring snapshots the target's current <code>.env</code> to
                            <code>storage/app/backups/</code> before overwriting it, as a safety net.</li>
                    </ul>
                </section>
            @endif

            <section id="roles" class="scroll-mt-4 bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-6">
                <h2>Roles &amp; permissions</h2>
                <p>Every account is either <strong>admin</strong> or <strong>user</strong>.</p>
                <table>
                    <thead>
                        <tr><th>Area</th><th>User</th><th>Admin</th></tr>
                    </thead>
                    <tbody>
                        <tr><td>Dashboard, Attendance, Students, Volunteers, Books, Data Entry, Reports, My Profile</td><td>&#10003;</td><td>&#10003;</td></tr>
                        <tr><td>Delete a student, guardian, school/grade record</td><td>&mdash;</td><td>&#10003;</td></tr>
                        <tr><td>Graduate a student</td><td>&mdash;</td><td>&#10003;</td></tr>
                        <tr><td>Settings (schools, grades, categories, job titles, imports, promotion)</td><td>&mdash;</td><td>&#10003;</td></tr>
                        <tr><td>Users (create/edit accounts, reset passwords)</td><td>&mdash;</td><td>&#10003;</td></tr>
                        <tr><td>Backup &amp; restore</td><td>&mdash;</td><td>&#10003;</td></tr>
                    </tbody>
                </table>
            </section>

            <section id="faq" class="scroll-mt-4 bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-6">
                <h2>Troubleshooting &amp; FAQ</h2>
                <dl>
                    <dt><strong>"Student already checked in"</strong></dt>
                    <dd>They have an open check-in for today already. Check them out first if you meant to reset
                        their session, or just leave it &mdash; they're already on-site.</dd>

                    <dt><strong>I can't select a student when renting a book</strong></dt>
                    <dd>Only students checked in <em>today</em>, or graduated alumni, can borrow. Check them in
                        first.</dd>

                    <dt><strong>The category/school/grade I need isn't in the dropdown</strong></dt>
                    <dd>Ask an admin to add it under Settings &mdash; these lists are managed there, not hardcoded.</dd>

                    <dt><strong>I forgot my password</strong></dt>
                    <dd>Ask an admin to reset it for you from your account's page under Users; you'll be asked to
                        choose a new one on your next login.</dd>

                    <dt><strong>I visited /restore and got a "not found" page</strong></dt>
                    <dd>That's expected once the install already has at least one user account &mdash; restore is
                        only reachable on a genuinely empty install, as a safety measure.</dd>
                </dl>
            </section>
        </div>
    </div>
</div>
