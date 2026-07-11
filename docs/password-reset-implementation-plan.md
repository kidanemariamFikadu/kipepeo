# Replace email-based invitations with admin-set passwords + forced reset on first login

## Context

The app was originally designed to run online, where inviting a user by email (a signed link, or a "click here to set your password" reset link) makes sense. It's now running offline, so outgoing mail never reaches anyone, and **both** of the app's existing "add a user" flows are broken as a result:

1. **`/invitation`** (`App\Livewire\User\Invitation`) — admin enters an email/role/job title, an `Invite` row is created with a token, and `InviteMail` is emailed to the invitee with a link to `/accept-invite/{token}`, where *they* set their own password. No `User` row exists until they click the link.
2. **`/user-create`** (`App\Livewire\User\CreateUser`) — creates the `User` row immediately with an unguessable random password (`bcrypt(Str::random(32))`), then calls `Password::sendResetLink()` so the user can email themselves a "set your password" link. Nobody — not even the admin — knows the password.

Per your decisions: replace both with **one** flow — the admin fills in the user's info *and* sets their initial password directly in the Create User form (no email involved), and the new user is forced to change that password the moment they first log in. Self-registration (`/register`, currently open via Fortify with no restriction) will also be disabled, since user creation should be admin-only.

## Implementation

### 1. Schema

New migration `add_must_reset_password_to_users_table`: adds `must_reset_password` (boolean, default `false`, after `job_title_id`). Default `false` matters — it must not retroactively flag the ~274 existing test users (and real existing users) created via `UserFactory`, which doesn't set this column.

New migration `drop_invites_table`: drops the now-unused `invites` table (`down()` recreates it identically to `2024_01_24_214438_create_invites_table.php`, for reversibility).

`app/Models/User.php`: add `must_reset_password` to `$fillable` and cast it `'boolean'`.

### 2. Admin creates a user with a real password

`app/Livewire/Forms/user/UserForm.php`: add `password` and `password_confirmation` properties; add to `rules()` using the same rule Fortify itself uses for consistency — `App\Actions\Fortify\PasswordValidationRules::passwordRules()` (`required|string|Password::default()|confirmed`, i.e. min 8 chars).

`app/Livewire/User/CreateUser.php`: replace the `Str::random(32)` + `Password::sendResetLink()` block with:
```php
$user = User::create([
    'name' => $this->form->name,
    'email' => $this->form->email,
    'job_title_id' => $this->form->job_title_id,
    'role' => $this->form->role,
    'password' => Hash::make($this->form->password),
    'must_reset_password' => true,
]);
```
Update the success toast to something like "User created. Share this password with them directly — they'll be asked to set their own on first login." No more `Password`/`Str` facade usage.

`resources/views/livewire/user/create-user.blade.php`: add Password + Confirm Password fields, styled like the existing Name/Email fields (`bg-gray-50 border ... wire:model='form.password'`, `@error('form.password')`), with small helper text ("At least 8 characters. The user will be asked to change this on first login.").

`resources/views/livewire/user-list.blade.php`: the "+ Invite user" button (`<a href="/invitation">`) becomes "+ Add user", dispatching `openModal` for `user.create-user` — same pattern already used by the row-level Edit button (`wire:click="$dispatch('openModal', { component: 'user.create-user' })"`).

`EditUser`/`EditUserForm` stay name/job title/role only, as today — password reset for an *existing* user gets its own dedicated action (below), kept separate from the general edit form since it's a different, more sensitive operation (rate: it changes what the user can log in with).

### 2b. Admin can reset an existing user's password

New `ModalComponent` `app/Livewire/User/ResetUserPassword.php`, same shape as `CreateUser`/`EditUser`:
```php
class ResetUserPassword extends ModalComponent
{
    public ResetPasswordForm $form; // new Form: password, password_confirmation — same passwordRules()
    public User $user;

    function mount(User $user) { $this->user = $user; }

    function reset()
    {
        $this->validate();
        $this->user->update([
            'password' => Hash::make($this->form->password),
            'must_reset_password' => true,
        ]);
        $this->dispatch('user-updated', ['type' => 'success', 'content' => "Password reset for {$this->user->name}. Share the new password with them directly — they'll be asked to set their own on next login."]);
        $this->closeModal();
    }

    public function render() { return view('livewire.user.reset-user-password'); }
}
```
New Form `app/Livewire/Forms/user/ResetPasswordForm.php` — just `password`/`password_confirmation`, validated with `PasswordValidationRules::passwordRules()` (same as the Create User form).

New view `resources/views/livewire/user/reset-user-password.blade.php` — small modal, same visual pattern as `create-user.blade.php`'s password fields (New Password / Confirm Password, same helper text about first-login reset), title "Reset Password for {{ $user->name }}".

Wired into `resources/views/livewire/user-list.blade.php`: a third action-column button (key icon) next to the existing Edit and History buttons, admin-only (this route is already admin-gated end-to-end), dispatching `openModal` for `user.reset-user-password` with `user: {{ $user->id }}` — same call shape as the existing Edit button.

### 3. Force a password change on first login

New middleware `app/Http/Middleware/RequirePasswordReset.php` (mirrors the existing `EnsureUserIsAdmin` middleware's shape):
```php
public function handle(Request $request, Closure $next): Response
{
    $user = $request->user();

    if ($user && $user->must_reset_password && ! $request->routeIs('force-password-reset', 'logout')) {
        return redirect()->route('force-password-reset');
    }

    return $next($request);
}
```
Registered as alias `'require-password-reset'` in `app/Http/Kernel.php`'s `$middlewareAliases`, and added to the main authenticated group's middleware array in `routes/web.php` (`['auth:sanctum', config('jetstream.auth_session'), 'verified', 'require-password-reset']`), so it guards every page in the app. No custom Fortify `LoginResponse` is needed — login already redirects to `/dashboard`, which will immediately bounce a flagged user to `/force-password-reset` on that very next page load.

New route (inside that same authenticated group, not the `admin` subgroup — any user may need it): `Route::get('/force-password-reset', ForcePasswordReset::class)->name('force-password-reset');`

New Livewire component `app/Livewire/User/ForcePasswordReset.php`, closely mirroring Jetstream's own `Laravel\Jetstream\Http\Livewire\UpdatePasswordForm` (the exact same proven mechanism already powering the "Update Password" card on `/my-profile` today) — reuses the already-bound `UpdatesUserPasswords` action (`App\Actions\Fortify\UpdateUserPassword`) so validation (current password check + password rules) is identical to the rest of the app:
```php
#[Layout('layouts.guest')]
class ForcePasswordReset extends Component
{
    public $state = ['current_password' => '', 'password' => '', 'password_confirmation' => ''];

    public function updatePassword(UpdatesUserPasswords $updater)
    {
        $this->resetErrorBag();
        $updater->update(Auth::user(), $this->state);

        if (request()->hasSession()) {
            request()->session()->put(['password_hash_' . Auth::getDefaultDriver() => Auth::user()->getAuthPassword()]);
        }

        return $this->redirect(route('dashboard'));
    }

    public function render() { return view('livewire.user.force-password-reset'); }
}
```
(The session `password_hash_web` refresh is required — without it, Laravel's `AuthenticateSession` middleware would log the user straight back out after they change their own password, since the session's remembered hash would no longer match.)

`App\Actions\Fortify\UpdateUserPassword::update()` gets one addition: also clear the flag on any successful password change, not just the forced one —
```php
$user->forceFill(['password' => Hash::make($input['password']), 'must_reset_password' => false])->save();
```
This means the flag also self-clears if an admin ever changes their own password from the normal Profile page while it happened to be set — one code path, no special-casing.

`resources/views/livewire/user/force-password-reset.blade.php`: a focused, centered card (mirrors the layout of the `accept-invite.blade.php` view being deleted, and uses `layouts.guest` like it did) — heading "Set a new password", explanatory line ("An administrator created this account for you. Enter the password they gave you, then choose a new one."), Current Password / New Password / Confirm Password fields (`wire:model='state.current_password'` etc., raw Tailwind inputs matching the rest of the app's custom forms), Save button.

### 4. Remove the email-based Invitation flow entirely

Delete (fully superseded, nothing else references them once `/invitation` and `/accept-invite/{token}` routes are gone):
- `app/Livewire/User/Invitation.php`, `app/Livewire/User/AcceptInvite.php`
- `app/Livewire/Forms/InvitationForm.php`, `app/Livewire/Forms/user/AcceptInviteForm.php`
- `app/Models/Invite.php`
- `app/Services/InvitationSerivce.php`
- `app/Mail/InviteMail.php`
- `resources/views/livewire/user/invitation.blade.php`, `resources/views/livewire/user/accept-invite.blade.php`
- `resources/views/emails/invite.blade.php`
- Tests: `tests/Feature/Livewire/User/InvitationTest.php`, `tests/Feature/Livewire/User/AcceptInviteTest.php`, `tests/Feature/Services/InvitationServiceTest.php`, `tests/Feature/Models/InviteTest.php`

`routes/web.php`: remove the `/accept-invite/{token}` route and the `/invitation` route, and their `use` imports.

`resources/views/components/layouts/app.blade.php`: remove the "Invitations" entry from `$adminLinks`.

`tests/Feature/AuthorizationTest.php`: remove `/invitation` from the admin-path list.

`tests/Feature/RoleValidationRuleTest.php`: remove the `use App\Livewire\User\Invitation;` import and the "invitation form rejects a role outside the UserRole enum" test; update the two `CreateUser` tests to also set `form.password`/`form.password_confirmation` (now required fields).

Also noticed and will delete as genuinely dead code either way: `app/Mail/UserRegisteredMail.php` (implements `ShouldQueue`, never dispatched anywhere).

### 5. Disable self-registration

`config/fortify.php`: remove `Features::registration()` from the `features` array. Leaves the scaffolded `resources/views/auth/register.blade.php` in place (harmless, Fortify just won't route to it) — not deleting stock Jetstream scaffolding that costs nothing to leave.

### 6. New/updated tests

- `tests/Feature/Livewire/User/ForcePasswordResetTest.php` (new): a user with `must_reset_password = true` who requests any authenticated page (e.g. `/dashboard`) gets redirected to `/force-password-reset`; a user with the flag `false` does not; submitting the correct current password + a valid new password on the reset page clears the flag and redirects to `/dashboard`; a wrong current password shows a validation error and does not clear the flag.
- `tests/Feature/Livewire/User/CreateUserTest.php` (new, since none exists today): admin creating a user sets a real usable password (assert `Hash::check` succeeds with the typed password against the created user) and sets `must_reset_password = true`; missing/short/mismatched password shows validation errors.
- `tests/Feature/Livewire/User/ResetUserPasswordTest.php` (new): admin resetting an existing user's password updates their password hash and sets `must_reset_password = true` (even if it was already `false`); a non-admin is forbidden (mirrors the existing `EditUser`/`Livewire::test(...)->assertForbidden()` convention); validation errors on short/mismatched password.
- Update `tests/Feature/RoleValidationRuleTest.php` and `tests/Feature/AuthorizationTest.php` as described above.
- Delete the four Invitation-flow test files listed above.
- Full `php artisan test` run afterward — expect the suite to shrink slightly (Invitation/AcceptInvite/InvitationService/InviteModel tests removed) but stay green, then grow back with the new ForcePasswordReset/CreateUser coverage.

## Verification

- `php artisan migrate` (adds the column, drops `invites`).
- `php artisan test` — full suite green.
- `npm run build`.
- Manual check flagged as usual (no browser tooling available): create a user via the modal with a chosen password, log in as them, confirm the forced reset page appears and can't be bypassed by navigating elsewhere, complete it, confirm landing on the dashboard and that the flag stays cleared on subsequent logins.

## Save plan to docs

Per the standing convention, save this plan to `docs/password-reset-implementation-plan.md` as the first implementation step.
