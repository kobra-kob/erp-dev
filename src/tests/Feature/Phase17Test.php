<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Company;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Phase17Test extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->company = Company::create(['name' => 'Demo']);
        $this->admin = User::create([
            'company_id' => $this->company->id, 'name' => 'Admin', 'email' => 'admin@test.local',
            'password' => Hash::make('password'), 'role' => User::ROLE_ADMIN,
        ]);
    }

    public function test_admin_updates_logo_and_document_theme(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin)->put(route('settings.branding.update'), [
            'logo'           => UploadedFile::fake()->image('logo.png'),
            'brand_color'    => '#ff8800',
            'brand_accent'   => '#222222',
            'document_shape' => 'square',
        ])->assertRedirect();

        $company = $this->company->fresh();
        $this->assertSame('#ff8800', $company->brand_color);
        $this->assertSame('square', $company->document_shape);
        $this->assertNotNull($company->logo);
        Storage::disk('public')->assertExists($company->logo);
        $this->assertSame('0', $company->documentRadius());
    }

    public function test_non_admin_cannot_change_branding(): void
    {
        $employee = User::create([
            'company_id' => $this->company->id, 'name' => 'Emp', 'email' => 'emp@test.local',
            'password' => Hash::make('password'), 'role' => User::ROLE_EMPLOYE,
        ]);

        $this->actingAs($employee)->put(route('settings.branding.update'), [
            'brand_color' => '#ffffff', 'brand_accent' => '#000000', 'document_shape' => 'rounded',
        ])->assertForbidden();
    }

    public function test_invalid_color_is_rejected(): void
    {
        $this->actingAs($this->admin)->put(route('settings.branding.update'), [
            'brand_color' => 'rouge', 'brand_accent' => '#000000', 'document_shape' => 'rounded',
        ])->assertSessionHasErrors('brand_color');
    }

    public function test_quote_pdf_renders_with_branding(): void
    {
        $this->company->update(['brand_color' => '#ff8800', 'brand_accent' => '#222222', 'document_shape' => 'square']);
        $client = Client::factory()->create(['company_id' => $this->company->id]);

        $quote = Quote::create([
            'company_id' => $this->company->id, 'client_id' => $client->id,
            'number' => 'DEV-0001', 'status' => 'draft', 'issue_date' => now()->toDateString(),
        ]);
        $quote->lines()->create([
            'type' => 'materiel', 'description' => 'Article', 'quantity' => 1,
            'unit_price' => 100, 'tax_rate' => 20, 'position' => 0,
        ]);
        $quote->recalculateTotals();

        $response = $this->actingAs($this->admin)->get(route('quotes.pdf', $quote));
        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
    }
}
