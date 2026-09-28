<?php

namespace App\Livewire;

use App\Livewire\Concerns\HasSortableColumns;
use App\Models\User;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class UserList extends Component
{
    use HasSortableColumns;
    use WithPagination;

    protected function sortableColumns(): array
    {
        return ['created_at', 'name', 'email', 'job_title_id', 'role'];
    }

    #[Url(history: true)]
    public $search = '';

    #[Url(history: true)]
    public $admin = '';

    #[Url(history: true)]
    public $sortBy = 'created_at';

    #[Url(history: true)]
    public $sortDir = 'DESC';

    #[Url()]
    public $perPage = 5;

    #[On('user-updated')]
    public function refreshUsers($message)
    {
        if ($message) {
            session()->flash($message['type'], $message['content']);
        }
    }

    public function render()
    {
        return view('livewire.user-list', [
            'users' => User::search($this->search)
                ->when($this->admin !== '', function ($query) {
                    $query->where('role', $this->admin);
                })
                ->orderBy($this->safeSortBy(), $this->safeSortDir())
                ->paginate($this->perPage)
        ])->title('Users');
    }
}
