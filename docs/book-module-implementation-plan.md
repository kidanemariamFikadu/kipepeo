# Book module v1 — rental history, delete action, working copy-count edits

## Context

Continuing the "each entity as a module" work (Student then Volunteer already brought up to a consistent detail-page standard — see `docs/student-module-implementation-plan.md`, `docs/volunteer-module-implementation-plan.md`). A research pass on Book found:

- `BookOnRent`/`book-on-rent.blade.php` (the global "who's borrowed what" list) is **already** wired up as a tab on `/books` (`resources/views/livewire/book/index.blade.php`) — not orphaned, no action needed there. (Corrects an assumption from an earlier session.)
- The clearest real gap: **`book-detail.blade.php` never shows rental history for that specific book**, even though `BookDetail::mount()` already eager-loads `$this->book->rentals` — the data is loaded and simply never rendered. This is the same class of gap Student and Volunteer both had (and already fixed) for their own history.
- The back button uses `onclick="goBack()"` / `window.history.back()` instead of `route('books')` like Student/Volunteer's detail pages — breaks if someone lands on the page via a direct link/bookmark rather than in-app navigation.
- No delete-book action exists on the detail page (only on the list page).
- The "copies" field on the detail page's edit form is **silently broken**: `BookDetail::mount()` populates `$this->copies` from the book, the form binds to it, but `BookDetail::update()` never includes `copies` in the `$this->book->update([...])` call — editing it does nothing. The user wants this to actually work, growing/shrinking the copy pool.

The user chose to fold in the rental history fix (core), the delete action, and fixing/completing the copy-count editing.

## Part 1 — Rental History card + back-button fix

**`app/Livewire/Book/BookDetail.php`**
- `mount()`: extend the eager-load to `Book::with(['bookCopies', 'rentals' => fn ($q) => $q->orderByDesc('rented_at')->limit(20), 'rentals.checkedOutTo'])->find($id)` (currently just `'bookCopies', 'rentals'`, no `checkedOutTo`, no ordering/limit).

**`resources/views/livewire/book/book-detail.blade.php`**
- Replace `<button onclick="goBack()">`/the trailing `<script>function goBack()...</script>` block with `<a href="{{ route('books') }}">`, matching `student-detail.blade.php`/`volunteer-detail.blade.php`'s back-link pattern exactly.
- Add a "Rental History" card (same `bg-white dark:bg-gray-800 shadow` shell already used by the Basic Information card on this page) between the edit form and the embedded `@livewire('book.copies', ...)`. Columns and status-badge logic copied directly from `book-on-rent.blade.php` (Student, Rented At, Due Date, Returned At, Status — Returned/Overdue/Borrowed, same color classes) — but the "Student" column shows `$rental->checkedOutTo?->name ?? '(student removed)'` instead of a book title, since the book itself is already the page subject. Include the same Return action (`wire:click="$dispatch('openModal', { component: 'book.return-book', arguments: { rentalId: ... }})"`) for unreturned rentals, exactly as added to Student's page — no new modal needed, `App\Livewire\Book\ReturnBook` already exists.
- Add a `#[On('rental-changed')]` no-op refresh listener to `BookDetail.php` (mirrors the one added to `StudentDetail.php`) so the table refreshes after a Return.

## Part 2 — Delete-book action on the detail page

**`app/Livewire/Book/BookDetail.php`**
- Add `deleteBook()`: `abort_unless(auth()->user()->isAdmin(), 403);` then `$this->book->delete();` then `session()->flash('success', 'Book deleted successfully.');` then `return $this->redirect(route('books'));` — same guard/flash message as `BookList::deleteBook()` (`app/Livewire/Book/BookList.php:42-48`), plus a redirect since the page's subject no longer exists after deletion (list page doesn't need this since it just re-renders without the row).

**`resources/views/livewire/book/book-detail.blade.php`**
- Add a "Delete book" button next to "Update" in the Basic Information form footer, admin-gated (`@if (auth()->user()->isAdmin())`), same `wire:confirm` + `wire:loading` + `<x-spinner>` pattern as `book-list.blade.php:113-124`.

## Part 3 — Make the copies field actually work (grow/shrink the copy pool)

**`app/Livewire/Book/BookDetail.php`** — extend `update()`:
- Add `'copies' => 'required|integer|min:1'` to the validation rules.
- Before saving, compute `$circulating = $this->book->bookCopies()->whereIn('status', ['borrowed', 'lost', 'stolen'])->count()` (copies not available for a straightforward removal).
- If `$this->copies < $circulating`: add a validation error via `$this->addError('copies', "Can't reduce copies below the {$circulating} currently borrowed, lost, or stolen.")` and `return` without saving — mirrors the existing `@error('copies')` display slot already in the form.
- If `$this->copies > $this->book->copies` (growing): create the difference as new `BookCopy::create(['book_id' => $this->book->id, 'status' => 'available'])` rows — identical loop to `CreateBook::create()` (`app/Livewire/Book/CreateBook.php:55-60`).
- If `$this->copies < $this->book->copies` (shrinking, already validated as safe): delete the difference from **available** copies only — `BookCopy::where('book_id', $this->book->id)->where('status', 'available')->latest('id')->take($old - $new)->pluck('id')` then `BookCopy::whereIn('id', $ids)->delete()` (`BookCopy` has no `SoftDeletes`, so this is a real hard delete of placeholder rows that were never borrowed — acceptable, matches how the model already treats copies as disposable records with no history of their own).
- Include `'copies' => $this->copies` in the existing `$this->book->update([...])` call.

**Tests**: extend `tests/Feature/Livewire/Book/BookDetailTest.php` covering: rental history renders a borrower + status badge; the Return action reaches the modal with the right `rentalId`; growing copies creates new available `BookCopy` rows and updates `available_copies`; shrinking copies removes available rows; shrinking below the circulating (borrowed/lost/stolen) count fails validation and leaves the book unchanged; `deleteBook()` soft-deletes and redirects, admin-gated (non-admin gets 403, mirrors any existing admin-gate test style e.g. in `CopiesTest.php`).

## Verification

- `php artisan test --filter=BookDetail` for the targeted page, then the full `php artisan test` suite (currently 262 passing) to confirm no regressions.
- `npm run build` to confirm no Blade/Tailwind issues.
- Manual browser check flagged as usual (no browser tooling): rental history + Return action, the new Delete button, and growing/shrinking copies, in both light/dark mode.
