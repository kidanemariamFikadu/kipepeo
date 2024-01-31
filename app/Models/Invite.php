<?php

namespace App\Models;

use App\Mail\InviteMail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Mail;

class Invite extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'token',
        'role',
        'job_title_id',
        'invited_by',
        'status',
        'expires_at',
    ];

    /**
     * Get the role that owns the Invite
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function jobTitle(): BelongsTo
    {
        return $this->belongsTo(JobTitle::class);
    }

    /**
     * Get the createdBy that owns the Invite
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class,'invited_by');
    }

    public function sendEmail()
    {
        $url = url('/accept-invite/' . $this->token);
        $data = [
            'url' => $url,
            'role' => $this->role,
            'expires_at' => $this->expires_at,
        ];
        Mail::to($this->email)->send(new InviteMail($data));
    }

    public function accept($name, $password)
    {
        $user = User::create([
            'name' => $name,
            'email' => $this->email,
            'password' => bcrypt($password),
            'role' => $this->role,
            'job_title_id' => $this->job_title_id,
            'email_verified_at' => now(),
        ]);

        $this->status = 'accepted';
        $this->save();
    }
}
