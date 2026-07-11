<?php

use App\Models\User;

test('main nav shows daily-use links plus a grouped Books dropdown, without an Admin dropdown for non-admins', function () {
    $user = User::factory()->create(['role' => 'user']);

    $response = $this->actingAs($user)->get('/');

    $response->assertOk();
    $response->assertSee('Home');
    $response->assertSee('Attendance');
    $response->assertSee('Volunteers');
    $response->assertSee('Students');
    $response->assertSee('Books');
    $response->assertSee('Book');
    $response->assertSee('Books on Loan');
    $response->assertSee('Data Entry');
    $response->assertSee('Report');
    $response->assertDontSee('Admin');
    $response->assertDontSee('Users');
});

test('main nav shows an Admin dropdown with Users and Settings for admins', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->get('/');

    $response->assertOk();
    $response->assertSee('Admin');
    $response->assertSee('Users');
    $response->assertSee('Settings');
    $response->assertDontSee('Invitations');
});
