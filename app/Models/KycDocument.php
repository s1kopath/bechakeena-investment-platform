<?php

namespace App\Models;

use App\Enums\KycDocumentStatus;
use App\Enums\KycDocumentType;
use Database\Factories\KycDocumentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'type',
    'document_number',
    'front_image_path',
    'back_image_path',
    'selfie_path',
    'status',
    'reviewed_by',
    'reviewed_at',
    'rejection_reason',
])]
#[Hidden([
    'document_number',
])]
class KycDocument extends Model
{
    /** @use HasFactory<KycDocumentFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => KycDocumentType::class,
            'status' => KycDocumentStatus::class,
            'document_number' => 'encrypted',
            'reviewed_at' => 'datetime',
        ];
    }

    /**
     * The investor who submitted this document.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
