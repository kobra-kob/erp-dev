<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Phase16Test extends TestCase
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
        $this->actingAs($this->admin);
    }

    public function test_product_created_with_image_and_catalog_fields(): void
    {
        Storage::fake('public');

        $this->post(route('products.store'), [
            'name' => 'Mitigeur inox', 'reference' => 'MIT-01', 'category' => 'Plomberie',
            'kind' => 'purchased', 'unit' => 'u', 'is_sellable' => '1',
            'purchase_price' => 30, 'sale_price' => 79.90, 'tax_rate' => 20,
            'stock' => 12, 'min_stock' => 3,
            'description' => 'Mitigeur monocommande, finition chromée.',
            'image' => UploadedFile::fake()->image('mitigeur.jpg'),
        ])->assertRedirect(route('products.index'));

        $product = Product::where('name', 'Mitigeur inox')->firstOrFail();

        $this->assertSame('Plomberie', $product->category);
        $this->assertTrue($product->is_sellable);
        $this->assertSame('purchased', $product->kind);
        $this->assertNotNull($product->image_path);
        Storage::disk('public')->assertExists($product->image_path);
    }

    public function test_product_sheet_displays_details(): void
    {
        $product = Product::create([
            'company_id' => $this->company->id, 'name' => 'Carrelage 30x30',
            'category' => 'Revêtement', 'kind' => 'purchased', 'unit' => 'm²',
            'purchase_price' => 8, 'sale_price' => 19.5, 'tax_rate' => 20, 'is_sellable' => true,
            'stock' => 50, 'min_stock' => 10, 'description' => 'Grès cérame antidérapant.',
        ]);

        $this->get(route('products.show', $product))->assertOk()
            ->assertSee('Carrelage 30x30')
            ->assertSee('Grès cérame antidérapant.')
            ->assertSee('Revêtement');
    }

    public function test_sellable_product_offered_in_quote_lines(): void
    {
        $sellable = Product::create([
            'company_id' => $this->company->id, 'name' => 'Robinet cuisine',
            'kind' => 'purchased', 'unit' => 'u', 'purchase_price' => 20, 'sale_price' => 49,
            'tax_rate' => 20, 'is_sellable' => true, 'stock' => 5, 'min_stock' => 1,
        ]);
        $hidden = Product::create([
            'company_id' => $this->company->id, 'name' => 'Composant interne',
            'kind' => 'manufactured', 'unit' => 'u', 'purchase_price' => 5, 'sale_price' => 0,
            'tax_rate' => 20, 'is_sellable' => false, 'stock' => 100, 'min_stock' => 0,
        ]);

        $this->get(route('quotes.create'))->assertOk()
            ->assertSee('Robinet cuisine')
            ->assertDontSee('Composant interne');
    }
}
