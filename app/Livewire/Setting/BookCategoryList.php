<?php

namespace App\Livewire\Setting;

use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class BookCategoryList extends Component
{
    #[On('book-category-changed')]
    public function bookCategoryChanged()
    {
    }

    #[On('MessageChanged')]
    public function messageChanged($message)
    {
        session()->flash($message['type'], $message['content']);
    }

    #[Computed]
    public function getBookCategoryListProperty()
    {
        return \App\Models\BookCategory::withCount('books')->orderBy('name')->get();
    }

    function removeBookCategory($bookCategoryId)
    {
        $bookCategory = \App\Models\BookCategory::find($bookCategoryId);

        if ($bookCategory->books()->exists()) {
            $this->dispatch('MessageChanged', ['type' => 'error', 'content' => 'Book category cannot be deleted as books are assigned to it']);
            return;
        }

        $bookCategory->delete();
        $this->dispatch('MessageChanged', ['type' => 'success', 'content' => 'Book category deleted successfully']);
    }

    public function render()
    {
        return view('livewire.setting.book-category-list');
    }
}
