<?php

namespace App\Services;

use App\Models\Invite;
use Valorin\Random\Random;

class InvitationSerivce
{
    public static function create($email, $role, $job_title_id,$invited_by)
    {
        $otp = Random::otp(8);
        $expires_at = now()->addDays(7);
        $invite = Invite::create([
            'email' => $email,
            'token' => $otp,
            'role' => $role,
            'job_title_id'=>$job_title_id,
            'invited_by' => $invited_by,
            'status' => 'pending',
            'expires_at' => $expires_at,
        ]);
        $invite->sendEmail();
    }

    public static function accept($token, $name, $password)
    {
        $invite = Invite::where('token', $token)->firstOrFail();
        $invite->accept($name, $password);
    }

    public static function resend($id)
    {
        $invite = Invite::where('id', $id)->firstOrFail();
        $invite->sendEmail();
    }

    public static function delete($token)
    {
        $invite = Invite::where('token', $token)->firstOrFail();
        $invite->delete();
    }

    public static function getInvites()
    {
        return Invite::get();
    }

    public static function getInvite($token)
    {
        return Invite::where('token', $token)->firstOrFail();
    }

    public static function getInviteByEmail($email)
    {
        return Invite::where('email', $email)->firstOrFail();
    }
}
