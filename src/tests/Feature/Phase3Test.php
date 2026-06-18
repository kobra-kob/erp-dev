<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Intervention;
use App\Models\Product;
use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Phase3Test extends TestCase
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

    private function employee(): User
    {
        return User::create([
            'company_id' => $this->company->id, 'name' => 'Emp', 'email' => 'emp@test.local',
            'password' => Hash::make('password'), 'role' => User::ROLE_EMPLOYE,
        ]);
    }

    public function test_product_low_stock_detection(): void
    {
        $low = new Product(['stock' => 3, 'min_stock' => 10]);
        $ok  = new Product(['stock' => 50, 'min_stock' => 10]);

        $this->assertTrue($low->isLowStock());
        $this->assertFalse($ok->isLowStock());
    }

    public function test_admin_can_create_and_adjust_product(): void
    {
        $this->actingAs($this->admin)->post(route('products.store'), [
            'name' => 'Tuyau', 'unit' => 'm', 'purchase_price' => 2, 'sale_price' => 6,
            'stock' => 5, 'min_stock' => 10,
        ])->assertRedirect();

        $product = Product::firstOrFail();
        $this->assertTrue($product->isLowStock());

        $this->actingAs($this->admin)->patch(route('products.adjust', $product), ['delta' => 20]);
        $this->assertEquals(25, $product->fresh()->stock);
    }

    public function test_employee_cannot_access_stock(): void
    {
        $this->actingAs($this->employee())->get(route('products.index'))->assertForbidden();
    }

    public function test_project_creation_and_comment(): void
    {
        $this->actingAs($this->admin)->post(route('projects.store'), [
            'name' => 'Chantier test', 'status' => 'in_progress', 'progress' => 40,
        ])->assertRedirect();

        $project = Project::firstOrFail();
        $this->assertSame($this->company->id, $project->company_id);

        $this->actingAs($this->admin)->post(route('projects.comments.store', $project), ['body' => 'Avancement OK'])
            ->assertRedirect();
        $this->assertDatabaseHas('project_comments', ['project_id' => $project->id, 'body' => 'Avancement OK']);
    }

    public function test_project_document_upload_and_secure_download(): void
    {
        Storage::fake('local');
        $project = Project::create(['company_id' => $this->company->id, 'name' => 'C', 'status' => 'planned', 'progress' => 0]);

        $this->actingAs($this->admin)->post(route('projects.documents.store', $project), [
            'file' => UploadedFile::fake()->image('photo.jpg'),
        ])->assertRedirect();

        $doc = ProjectDocument::firstOrFail();
        Storage::assertExists($doc->path);

        $this->actingAs($this->admin)->get(route('projects.documents.show', [$project, $doc]))->assertOk();
    }

    public function test_employee_can_access_planning_and_projects(): void
    {
        $emp = $this->employee();
        $this->actingAs($emp)->get(route('projects.index'))->assertOk();
        $this->actingAs($emp)->get(route('interventions.index'))->assertOk();
    }

    public function test_intervention_creation_and_events_feed(): void
    {
        $this->actingAs($this->admin)->post(route('interventions.store'), [
            'title' => 'Dépannage', 'status' => 'planned',
            'start_at' => now()->setTime(9, 0)->format('Y-m-d H:i'),
            'end_at'   => now()->setTime(11, 0)->format('Y-m-d H:i'),
        ])->assertRedirect();

        $this->assertSame(1, Intervention::count());

        $this->actingAs($this->admin)->getJson(route('interventions.events'))
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment(['title' => 'Dépannage']);
    }

    public function test_interventions_are_isolated_by_company(): void
    {
        $other = Company::create(['name' => 'Other']);
        Intervention::create([
            'company_id' => $other->id, 'title' => 'Autre boite', 'status' => 'planned',
            'start_at' => now(), 'end_at' => now()->addHour(),
        ]);

        $this->actingAs($this->admin)->getJson(route('interventions.events'))->assertJsonCount(0);
    }
}
