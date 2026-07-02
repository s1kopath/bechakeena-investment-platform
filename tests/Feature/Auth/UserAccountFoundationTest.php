<?php

namespace Tests\Feature\Auth;

use App\Enums\KycDocumentStatus;
use App\Enums\KycStatus;
use App\Enums\UserStatus;
use App\Models\KycDocument;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserAccountFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_user_defaults_to_pending_kyc_and_active_status(): void
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(KycStatus::class, $user->kyc_status);
        $this->assertSame(KycStatus::Pending, $user->kyc_status);
        $this->assertSame(UserStatus::Active, $user->status);
        $this->assertFalse($user->isKycApproved());
    }

    public function test_user_implements_must_verify_email(): void
    {
        $this->assertInstanceOf(MustVerifyEmail::class, User::factory()->make());
    }

    public function test_sensitive_fields_are_encrypted_at_rest(): void
    {
        $user = User::factory()->create([
            'nid_number' => '1990123456789',
            'bank_account_number' => '00110022003300',
        ]);

        // Accessor decrypts transparently.
        $this->assertSame('1990123456789', $user->fresh()->nid_number);
        $this->assertSame('00110022003300', $user->fresh()->bank_account_number);

        // Raw DB value is ciphertext, not the plaintext.
        $raw = DB::table('users')->where('id', $user->id)->first();
        $this->assertNotSame('1990123456789', $raw->nid_number);
        $this->assertNotSame('00110022003300', $raw->bank_account_number);
    }

    public function test_hidden_fields_are_excluded_from_array(): void
    {
        $user = User::factory()->create([
            'nid_number' => '1990123456789',
            'bank_account_number' => '00110022003300',
        ]);

        $array = $user->toArray();

        $this->assertArrayNotHasKey('password', $array);
        $this->assertArrayNotHasKey('nid_number', $array);
        $this->assertArrayNotHasKey('passport_number', $array);
        $this->assertArrayNotHasKey('bank_account_number', $array);
        $this->assertArrayNotHasKey('google_id', $array);
    }

    public function test_user_has_many_kyc_documents_with_enum_casts(): void
    {
        $user = User::factory()->kycSubmitted()->create();
        $doc = KycDocument::factory()->for($user)->create([
            'document_number' => 'DOC-778899',
        ]);

        $this->assertTrue($user->kycDocuments()->exists());
        $this->assertSame(KycDocumentStatus::Pending, $doc->status);
        $this->assertSame('DOC-778899', $doc->fresh()->document_number);

        // document_number is encrypted at rest.
        $raw = DB::table('kyc_documents')->where('id', $doc->id)->first();
        $this->assertNotSame('DOC-778899', $raw->document_number);
    }

    public function test_google_state_creates_passwordless_account(): void
    {
        $user = User::factory()->google()->create();

        $this->assertNull($user->password);
        $this->assertNotNull($user->google_id);
        $this->assertNotNull($user->email_verified_at);
    }
}
