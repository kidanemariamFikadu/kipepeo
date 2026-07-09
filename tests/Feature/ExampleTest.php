<?php

it('redirects guests visiting the root URL to login', function () {
    // Two `Route::get('/', ...)` closures are registered in routes/web.php - one open
    // (returns the login view) and one inside the auth middleware group (the dashboard).
    // Laravel's route collection keys static routes by method+URI, so the second
    // registration silently overwrites the first, and the "always show login" route is
    // dead code. This test documents actual behavior: guests get redirected, not a 200.
    $response = $this->get('/');

    $response->assertRedirect('/login');
});

it('shows the dashboard for authenticated users at the root URL', function () {
    $user = \App\Models\User::factory()->create();

    $response = $this->actingAs($user)->get('/');

    $response->assertOk();
});
