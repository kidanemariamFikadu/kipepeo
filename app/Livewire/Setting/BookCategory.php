<?php

namespace App\Livewire\Setting;

use Illuminate\Validation\Rule;
use LivewireUI\Modal\ModalComponent;

class BookCategory extends ModalComponent
{
    public $name;

    public $bookCategoryId;

    public function mount($bookCategoryId = null)
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $this->bookCategoryId = $bookCategoryId;
        if ($bookCategoryId) {
            $bookCategory = \App\Models\BookCategory::find($bookCategoryId);
            $this->name = $bookCategory->name;
        }
    }

    function saveBookCategory()
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $this->validate([
            'name' => ['required', 'min:2', 'max:255', Rule::unique('book_categories', 'name')->ignore($this->bookCategoryId)],
        ]);

        if ($this->bookCategoryId) {
            \App\Models\BookCategory::find($this->bookCategoryId)->update([
                'name' => $this->name,
            ]);
            $this->dispatch('MessageChanged', ['type' => 'success', 'content' => 'Book category updated successfully']);
        } else {
            \App\Models\BookCategory::create([
                'name' => $this->name,
            ]);
            $this->dispatch('MessageChanged', ['type' => 'success', 'content' => 'Book category created successfully']);
        }

        $this->dispatch('book-category-changed');
        $this->closeModal();
    }

    public function render()
    {
        return view('livewire.setting.book-category');
    }
}
