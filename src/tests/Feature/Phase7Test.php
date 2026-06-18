<?php

namespace Tests\Feature;

use App\Mail\SupplierOrderMail;
use App\Models\Client;
use App\Models\Company;
use App\Models\Expense;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Quote;
use App\Models\User;
use App\Services\AssistantService;
use App\Services\StockReplenishmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class Phase7Test extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('assistant.api_key', ''); // mode local déterministe
        $this->company = Company::create(['name' => 'Demo']);
        $this->admin = User::create([
            'company_id' => $this->company->id, 'name' => 'Admin', 'email' => 'admin@test.local',
            'password' => Hash::make('password'), 'role' => User::ROLE_ADMIN,
        ]);
        $this->actingAs($this->admin);
    }

    private function product(array $attrs = []): Product
    {
        return Product::create(array_merge([
            'company_id' => $this->company->id, 'name' => 'Tuyau', 'unit' => 'm',
            'purchase_price' => 2, 'sale_price' => 6, 'stock' => 2, 'min_stock' => 10,
            'supplier_name' => 'Sanitaire Pro', 'supplier_email' => 'commande@sanitaire.fr',
            'reorder_quantity' => 50,
        ], $attrs));
    }

    // --- Dépense matériel reliée au stock ---

    public function test_material_expense_with_product_increments_stock(): void
    {
        $product = $this->product(['stock' => 5]);

        $this->post(route('expenses.store'), [
            'category' => 'materiel', 'label' => 'Achat tuyaux', 'amount' => 100,
            'quantity' => 10, 'product_id' => $product->id, 'spent_at' => now()->toDateString(),
        ])->assertRedirect();

        $this->assertEquals(15, $product->fresh()->stock); // 5 + 10
    }

    public function test_expense_can_be_linked_to_quote(): void
    {
        $client = Client::factory()->create(['company_id' => $this->company->id]);
        $quote = Quote::create([
            'company_id' => $this->company->id, 'client_id' => $client->id,
            'number' => 'DEV-X', 'status' => 'accepted', 'issue_date' => now(),
        ]);

        $this->post(route('expenses.store'), [
            'category' => 'deplacement', 'label' => 'Trajet chantier', 'amount' => 35,
            'spent_at' => now()->toDateString(), 'quote_id' => $quote->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('expenses', ['quote_id' => $quote->id, 'category' => 'deplacement']);
    }

    // --- Réapprovisionnement ---

    public function test_replenishment_orders_emails_and_restocks(): void
    {
        Mail::fake();
        $product = $this->product(['stock' => 2, 'reorder_quantity' => 50]);

        $order = app(StockReplenishmentService::class)->replenish($product);

        $this->assertEquals(52, $product->fresh()->stock);          // 2 + 50
        $this->assertInstanceOf(PurchaseOrder::class, $order);
        $this->assertDatabaseHas('purchase_orders', ['product_id' => $product->id, 'quantity' => 50]);
        $this->assertDatabaseHas('expenses', ['product_id' => $product->id, 'category' => 'materiel']);
        Mail::assertSent(SupplierOrderMail::class);
    }

    public function test_replenish_all_only_targets_low_stock_with_supplier(): void
    {
        Mail::fake();
        $this->product(['name' => 'Bas', 'stock' => 1, 'min_stock' => 10]);                  // éligible
        $this->product(['name' => 'OK', 'stock' => 100, 'min_stock' => 10]);                 // stock OK
        $this->product(['name' => 'SansMail', 'stock' => 1, 'min_stock' => 10, 'supplier_email' => null]); // pas d'e-mail

        $orders = app(StockReplenishmentService::class)->replenishLowStock();

        $this->assertCount(1, $orders);
        $this->assertSame('Bas', $orders->first()->product->name);
    }

    public function test_replenish_route_requires_supplier(): void
    {
        $noSupplier = $this->product(['stock' => 1, 'supplier_email' => null]);
        $this->post(route('products.replenish', $noSupplier))->assertSessionHasErrors('stock');
    }

    // --- Assistant : commande pilotée par l'IA ---

    public function test_assistant_reorder_intent_triggers_replenishment(): void
    {
        Mail::fake();
        $this->product(['stock' => 2, 'min_stock' => 10, 'reorder_quantity' => 40]);

        $result = app(AssistantService::class)->ask('commande le stock faible');

        $this->assertSame('action', $result['source']);
        $this->assertStringContainsString('commande', mb_strtolower($result['answer']));
        $this->assertSame(1, PurchaseOrder::count());
        Mail::assertSent(SupplierOrderMail::class);
    }

    public function test_assistant_signals_low_stock_with_supplier(): void
    {
        $this->product(['stock' => 2, 'min_stock' => 10]);

        $result = app(AssistantService::class)->ask('Quels produits sont en stock faible ?');

        $this->assertStringContainsString('Sanitaire Pro', $result['answer']);
    }
}
