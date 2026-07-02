<?php

namespace App\Models;

use App\Enums\KycStatus;
use App\Enums\UserStatus;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'email',
    'phone',
    'password',
    'nid_number',
    'passport_number',
    'bank_account_name',
    'bank_account_number',
    'bank_name',
    'bank_branch',
    'routing_number',
])]
#[Hidden([
    'password',
    'remember_token',
    'google_id',
    'nid_number',
    'passport_number',
    'bank_account_number',
])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'kyc_status' => KycStatus::class,
            'status' => UserStatus::class,
            'nid_number' => 'encrypted',
            'passport_number' => 'encrypted',
            'bank_account_number' => 'encrypted',
        ];
    }

    /**
     * KYC documents this investor has submitted.
     *
     * @return HasMany<KycDocument, $this>
     */
    public function kycDocuments(): HasMany
    {
        return $this->hasMany(KycDocument::class);
    }

    /**
     * Whether the investor has cleared KYC and may invest.
     */
    public function isKycApproved(): bool
    {
        return $this->kyc_status === KycStatus::Approved;
    }
}
