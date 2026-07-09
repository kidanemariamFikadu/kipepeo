<?php

use App\Livewire\Setting\ImportBook;
use App\Livewire\Setting\ImportStudents;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Livewire\Livewire;

// Writes real bytes to a real temp file and wraps it in an UploadedFile built
// the way Symfony does for genuine uploads, so getMimeType() performs actual
// content sniffing instead of trusting the filename extension. Using
// UploadedFile::fake()->createWithContent() would NOT exercise this: that
// helper fakes getMimeType() from the given filename's extension rather than
// sniffing the real bytes, which would make this test pass for the wrong
// reason. The dynamic ->name property is added because Livewire's file-upload
// test helper (Testable::set()) expects it.
function uploadedFileWithRealContent(string $originalName, string $content): UploadedFile
{
    $path = tempnam(sys_get_temp_dir(), 'test') . '_' . $originalName;
    file_put_contents($path, $content);

    $file = new UploadedFile($path, $originalName, null, null, true);
    $file->name = $originalName;

    return $file;
}

function fakeSpoofedXlsx(): UploadedFile
{
    // A PNG file signature, renamed with an .xlsx extension.
    $pngSignature = "\x89PNG\r\n\x1a\n" . str_repeat("\x00", 200);

    return uploadedFileWithRealContent('virus.xlsx', $pngSignature);
}

function fakeGenuineCsv(): UploadedFile
{
    return uploadedFileWithRealContent('books.csv', "title,author\nBook One,Author One\n");
}

test('book import rejects a spoofed file with the right extension but wrong content', function () {
    $user = User::factory()->create(['role' => 'admin']);

    Livewire::actingAs($user)
        ->test(ImportBook::class)
        ->set('books', fakeSpoofedXlsx())
        ->call('importBooks')
        ->assertHasErrors(['books']);
});

test('student import rejects a spoofed file with the right extension but wrong content', function () {
    $user = User::factory()->create(['role' => 'admin']);

    Livewire::actingAs($user)
        ->test(ImportStudents::class)
        ->set('students', fakeSpoofedXlsx())
        ->call('importStudents')
        ->assertHasErrors(['students']);
});

test('a genuine csv file passes the shared spreadsheet validation rule', function () {
    $rules = (new ImportBook)->spreadsheetUploadRules();

    $validator = Validator::make(['books' => fakeGenuineCsv()], ['books' => $rules]);

    expect($validator->fails())->toBeFalse();
});

test('extensions rule alone would NOT have caught the spoofed file (proves mimetypes: is the fix)', function () {
    $validator = Validator::make(
        ['books' => fakeSpoofedXlsx()],
        ['books' => 'file|extensions:xlsx,xls,csv'],
    );

    expect($validator->fails())->toBeFalse();
});
