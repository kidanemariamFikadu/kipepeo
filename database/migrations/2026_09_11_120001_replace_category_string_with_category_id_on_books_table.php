<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('class')->constrained('book_categories')->nullOnDelete();
        });

        // Every distinct category string already in use becomes a real row,
        // and existing books are pointed at it, so no data is lost.
        DB::table('books')->whereNotNull('category')->select('category')->distinct()->orderBy('category')->get()
            ->each(function ($row) {
                $categoryId = DB::table('book_categories')->insertGetId([
                    'name' => $row->category,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('books')->where('category', $row->category)->update(['category_id' => $categoryId]);
            });

        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->string('category')->nullable()->after('class');
        });

        DB::table('books')
            ->join('book_categories', 'books.category_id', '=', 'book_categories.id')
            ->update(['books.category' => DB::raw('book_categories.name')]);

        Schema::table('books', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
        });
    }
};
