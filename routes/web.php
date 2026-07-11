<?php

use App\Livewire\Attendance\AttendanceStudent;
use App\Livewire\Attendance\AttendanceVolunteer;
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

Route::get('/accept-invite/{token}', \App\Livewire\User\AcceptInvite::class)->name('accept-invite');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/', \App\Livewire\dashboard\Index::class);
    Route::get('/dashboard', \App\Livewire\dashboard\Index::class)->name('dashboard');
    Route::get('/my-profile', MyProfile::class)->name('my-profile');
    Route::get('/students', StudentList::class)->name('students');
    Route::get('/student-detail/{student_id}', StudentDetail::class)->name('student-detail');
    Route::get('/attendance', AttendanceStudent::class)->name('attendance');
    Route::get('/volunteers', AttendanceVolunteer::class)->name('volunteers');
    Route::get('/data-entry', \App\Livewire\DataEntry\Index::class)->name('data-entry');
    Route::get('/report', \App\Livewire\Report\Index::class)->name('report');
    Route::get('/books', \App\Livewire\Book\Index::class)->name('books');
    Route::get('/book-detail/{id}', \App\Livewire\Book\BookDetail::class)->name('book-detail');

    Route::middleware(['admin'])->group(function () {
        Route::get('/users', UserList::class)->name('users');
        Route::get('/user-create', CreateUser::class)->name('Create User');
        Route::get('/edit-user/{user_id}', EditUser::class)->name('edit-user');
        Route::get('/invitation', Invitation::class)->name('invitation');
        Route::get('/settings', \App\Livewire\Setting\Index::class)->name('settings');
        Route::get('/settings/schools', \App\Livewire\Setting\SchoolList::class)->name('settings-schools');
        Route::get('/settings/grades', \App\Livewire\Setting\GradeList::class)->name('settings-grades');
        Route::get('/settings/volunteers', \App\Livewire\Setting\VolunteerList::class)->name('settings-volunteers');
        Route::get('/settings/activity-types', \App\Livewire\Setting\ActivityTypeList::class)->name('settings-activity-types');
        Route::get('/settings/job-titles', \App\Livewire\Setting\JobTitleList::class)->name('settings-job-titles');
        Route::get('/settings/import-students', \App\Livewire\Setting\ImportStudents::class)->name('settings-import-students');
        Route::get('/settings/import-books', \App\Livewire\Setting\ImportBook::class)->name('settings-import-books');
        Route::get('/promote-students', \App\Livewire\Setting\PromoteStudents::class)->name('promote-students');
    });
});
