# Laravel Upgrade Evaluation: 10.42 → 12.x (→13.x)

**Bottom line: worth doing, and somewhat urgent — Laravel 10's security support has already ended.** The app itself is structurally clean (standard Kernel/Handler, no exotic overrides), and there are ~270 Pest tests as a safety net. The real risk isn't the app's code — it's third-party package compatibility.

## Why 12, not straight to 13

Laravel 13 (released March 2026) is the newest, but it shipped with **zero breaking changes over 12** — so 12→13 should be nearly free once on 12. The ecosystem (especially Livewire and smaller packages) has had more time to catch up to 12. Recommended path: **10 → 11 → 12**, then treat 13 as a low-cost follow-up.

## Dependency compatibility matrix

| Package | Current | Needed for L12/13 | Risk |
|---|---|---|---|
| laravel/framework | 10.42 | ^12.0 | — |
| laravel/jetstream | 4.2.2 | ^5.3+ | Low — confirmed L11/12/13 support |
| laravel/fortify | 1.20 (via Jetstream) | updates with Jetstream | Low |
| laravel/sanctum | 3.3.3 | ^4.0 | Low-medium — check stateful-domain config against Jetstream 5 |
| livewire/livewire | 3.4.0 | stay on 3.x | Medium — see below |
| wire-elements/modal | 2.0.9 | depends on Livewire 3.x staying supported | Medium — see below |
| owen-it/laravel-auditing | 13.6.4 | ^14.0 | Low |
| propaganistas/laravel-phone | 5.1.1 | ^6.0 | Low |
| maatwebsite/excel | 3.1.53 | 3.1.x claims support, but | High — users report a live `BadMethodCallException` on L12 |
| valorin/random | 0.4 | — | None — dead dependency, drop it (only used by the email-invite flow removed this session) |
| asantibanez/livewire-charts | 3.0.1 | unclear/community fork only | Drop — appears unused. Dashboard charts push data via Chart.js/JS events, not this package's Blade components. |

## The two things that actually decide how hard this is

1. **Livewire 3 vs 4.** Livewire 4 is the current headline version for Laravel 13, but Livewire 3.x continues to get releases supporting 10–13. This app has deep Livewire 3 investment — Form objects, `#[On]` attributes, `wire-elements/modal`'s `ModalComponent` across ~30 components. Moving to Livewire 4 would be a second, separate, much larger migration (different component/attribute conventions) — don't bundle it with the Laravel bump. Confirm a Livewire 3.x release exists that's compatible with L12 before starting; if not, this becomes the real blocker.

2. **maatwebsite/excel.** This is the one package with a reported, reproduced L12 incompatibility (a deprecated `Application::share()` call), used for student/book bulk imports. Spike-test this specifically before committing. If it's broken, options are pinning an older release or swapping to a lighter CSV/Excel library for those two import screens.

## What doesn't need to change

No `bootstrap/app.php` restructuring is required — Laravel 11+ still runs the classic `app/Http/Kernel.php` structure fine. Custom `require-password-reset`/`admin` middleware aliases, Fortify actions, and `Handler.php` all use stable, version-independent APIs. This isn't a rewrite — it's a dependency and deprecation pass.

## Recommended approach

1. Do it in a separate branch, not on `main`.
2. Drop `valorin/random` and (after confirming) `asantibanez/livewire-charts` first — fewer things to worry about.
3. Spike-test `maatwebsite/excel` against Laravel 12 in isolation before going further.
4. `10 → 11`: follow Laravel's official upgrade guide, `composer update`, run the full Pest suite, fix deprecations.
5. Bump Jetstream/Sanctum/auditing/phone to their L11-compatible majors.
6. `11 → 12`: repeat.
7. `12 → 13`: should be close to a version-number bump given the "zero breaking changes" claim — verify, don't assume.
8. Full test suite + a manual smoke test after each step, not just at the end.

**Effort estimate: medium.** The app's size and existing test coverage make this tractable in a focused effort, not a rewrite — but not small either, given three major-version jumps and one package with a known live issue.
