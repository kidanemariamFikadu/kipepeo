<div>
    <div class="p-2 md:p-6">
        <h2 class="text-2xl font-semibold text-gray-700 dark:text-white mb-4">My Profile</h2>
        <div class="space-y-6">
            @if (Laravel\Fortify\Features::canUpdateProfileInformation())
                @livewire('profile.update-profile-information-form')
            @endif

            @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
                @livewire('profile.update-password-form')
            @endif

            @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                @livewire('profile.two-factor-authentication-form')
            @endif

            @livewire('profile.logout-other-browser-sessions-form')
        </div>
    </div>
</div>
