<x-mail::message>
# Invitation

You have been invited to join our platform as a {{ $data['role'] }}.
Please click the button below to accept the invitation:

<x-mail::button :url="$data['url']">
Accept Invitation
</x-mail::button>

This invitation will expire on {{ $data['expires_at'] }}.
Thanks,<br>

The Platform Team
{{ config('app.name') }}
</x-mail::message>
