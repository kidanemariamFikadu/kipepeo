<?php

use App\Livewire\Attendance\AttendanceStudent;
use App\Livewire\Student\StudentDetail;
use App\Livewire\StudentList;
use App\Livewire\Attendance\StudentList as Attendance;
use App\Livewire\User\CreateUser;
use App\Livewire\User\EditUser;
use App\Livewire\User\Invitation;
use App\Livewire\User\MyProfile;
use App\Livewire\UserList;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/accept-invite/{token}', \App\Livewire\User\AcceptInvite::class)->name('accept-invite');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/', UserList::class)->name('dashboard');
    Route::get('/users', UserList::class)->name('users');
    Route::get('/user-create', CreateUser::class)->name('Create User');
    Route::get('/edit-user/{user_id}', EditUser::class)->name('edit-user');
    Route::get('/invitation', Invitation::class)->name('invitation');
    Route::get('/my-profile', MyProfile::class)->name('my-profile');
    Route::get('/students', StudentList::class)->name('students');
    Route::get('/student-detail/{student_id}', StudentDetail::class)->name('student-detail');
    Route::get('/attendance', AttendanceStudent::class)->name('attendance');

});
