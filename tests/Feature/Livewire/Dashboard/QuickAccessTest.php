<?php

use App\Models\User;

test('the dashboard quick access buttons open the right modal components', function () {
    $user = User::factory()->create();

    $html = $this->actingAs($user)->get('/')->getContent();

    expect($html)->toContain("component: 'attendance.quick-check-in-students'");
    expect($html)->toContain("component: 'attendance.quick-check-in-volunteers'");
    expect($html)->toContain("component: 'student.create-student'");
    expect($html)->toContain("component: 'book.rent'");
    expect($html)->not->toContain('quick-data-entry');
});
