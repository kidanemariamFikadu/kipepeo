<?php

namespace App\Livewire\Setting;

use Livewire\Attributes\Computed;
use Livewire\Component;

class Index extends Component
{
    #[Computed]
    public function schoolCount()
    {
        return \App\Models\School::count();
    }

    #[Computed]
    public function gradeCount()
    {
        return \App\Models\Grade::count();
    }

    #[Computed]
    public function volunteerCount()
    {
        return \App\Models\Volunteer::count();
    }

    #[Computed]
    public function activityTypeCount()
    {
        return \App\Models\ActivityType::count();
    }

    #[Computed]
    public function jobTitleCount()
    {
        return \App\Models\JobTitle::count();
    }

    #[Computed]
    public function bookCategoryCount()
    {
        return \App\Models\BookCategory::count();
    }

    public function render()
    {
        return view('livewire.setting.index')->title('Settings');
    }
}
