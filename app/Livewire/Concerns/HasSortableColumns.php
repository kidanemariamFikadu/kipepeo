<?php

namespace App\Livewire\Concerns;

trait HasSortableColumns
{
    /**
     * Columns this list is allowed to sort by, most-sensible default first.
     *
     * @return array<int, string>
     */
    abstract protected function sortableColumns(): array;

    public function setSortBy($sortByField)
    {
        if (! in_array($sortByField, $this->sortableColumns(), true)) {
            return;
        }

        if ($this->sortBy === $sortByField) {
            $this->sortDir = ($this->sortDir === 'ASC') ? 'DESC' : 'ASC';
            return;
        }

        $this->sortBy = $sortByField;
        $this->sortDir = 'DESC';
    }

    /**
     * $sortBy and $sortDir are public Livewire properties, so any signed-in
     * user can set them to arbitrary strings over /livewire/update without
     * going through setSortBy(). Eloquent quotes identifiers, so this is not
     * an injection point, but an unknown column raises a SQL error that
     * confirms which column names exist. Resolve through these instead of
     * reading the properties directly.
     */
    protected function safeSortBy(): string
    {
        return in_array($this->sortBy, $this->sortableColumns(), true)
            ? $this->sortBy
            : ($this->sortableColumns()[0] ?? 'id');
    }

    protected function safeSortDir(): string
    {
        return strtoupper((string) $this->sortDir) === 'ASC' ? 'ASC' : 'DESC';
    }
}
