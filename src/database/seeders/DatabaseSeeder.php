<?php

namespace Database\Seeders;

use App\Models\CatalogItem;
use App\Models\Client;
use App\Models\Company;
use App\Models\Expense;
use App\Models\Intervention;
use App\Models\Invoice;
use App\Models\LeaveRequest;
use App\Models\Product;
use App\Models\Project;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // --- Entreprise de démonstration ---
        $company = Company::create([
            'name'         => 'Plomberie Dupont',
            'siret'        => '12345678900012',
            'address'      => '12 rue des Artisans',
            'city'         => 'Lyon',
            'zip'          => '69003',
            'phone'        => '04 78 00 00 00',
            'email'        => 'contact@plomberie-dupont.fr',
            'subscription' => 'pro',
        ]);

        // --- Utilisateurs (un par rôle) ---
        $admin = User::create([
            'company_id' => $company->id,
            'name'       => 'Louis Dupont',
            'email'      => 'louis@artisanflow.test',
            'password'   => Hash::make('password'),
            'role'       => User::ROLE_ADMIN,
            'phone'      => '06 12 34 56 78',
            'skill'      => 'Gérant',
        ]);
        $company->update(['owner_id' => $admin->id]);

        User::create([
            'company_id' => $company->id,
            'name'       => 'Marie Martin',
            'email'      => 'marie@artisanflow.test',
            'password'   => Hash::make('password'),
            'role'       => User::ROLE_GERANT,
            'phone'      => '06 22 33 44 55',
        ]);

        $paul = User::create([
            'company_id' => $company->id,
            'name'       => 'Paul Bernard',
            'email'      => 'paul@artisanflow.test',
            'password'   => Hash::make('password'),
            'role'       => User::ROLE_EMPLOYE,
            'skill'      => 'Plombier',
            'phone'      => '06 99 88 77 66',
        ]);

        // --- Clients de démonstration ---
        $clients = Client::factory()->count(15)->create(['company_id' => $company->id]);

        // --- Devis & factures de démonstration (entreprise 1) ---
        $this->seedQuotesAndInvoices($company->id, $clients);

        // --- Stock, chantiers & planning (Phase 3) ---
        $this->seedPhase3($company->id, $clients, $paul);

        // --- Module optionnel Bâtiment activé + catalogue de prestations démo ---
        $company->enableModule('batiment');
        $catalog = [
            ['plombier', 'materiel', 'Mitigeur thermostatique', 'u', 89, 20],
            ['plombier', 'main_oeuvre', 'Pose de mitigeur', 'h', 45, 20],
            ['electricien', 'materiel', 'Tableau électrique 13 modules', 'u', 79, 20],
            ['electricien', 'main_oeuvre', 'Mise aux normes prise', 'u', 35, 20],
            ['peintre', 'main_oeuvre', 'Peinture murs (2 couches)', 'm²', 18, 10],
        ];
        foreach ($catalog as [$trade, $type, $label, $unit, $price, $tva]) {
            CatalogItem::create([
                'company_id' => $company->id, 'trade' => $trade, 'line_type' => $type,
                'label' => $label, 'unit' => $unit, 'unit_price' => $price, 'tax_rate' => $tva,
            ]);
        }

        // --- Demandes de congés de démonstration ---
        LeaveRequest::create([
            'company_id' => $company->id, 'user_id' => $paul->id, 'type' => 'conges_payes',
            'start_date' => now()->addWeeks(3)->toDateString(), 'end_date' => now()->addWeeks(4)->toDateString(),
            'reason' => 'Vacances d\'été', 'status' => 'pending',
        ]);
        LeaveRequest::create([
            'company_id' => $company->id, 'user_id' => $paul->id, 'type' => 'rtt',
            'start_date' => now()->subWeeks(2)->toDateString(), 'end_date' => now()->subWeeks(2)->addDay()->toDateString(),
            'status' => 'approved', 'reviewed_by' => $admin->id, 'reviewed_at' => now()->subWeeks(3),
            'review_comment' => 'OK, bon repos',
        ]);

        // --- Seconde entreprise (pour prouver l'isolation des données) ---
        $other = Company::create(['name' => 'Élec Pro', 'subscription' => 'free']);
        $sophie = User::create([
            'company_id' => $other->id,
            'name'       => 'Sophie Leroy',
            'email'      => 'sophie@elecpro.test',
            'password'   => Hash::make('password'),
            'role'       => User::ROLE_ADMIN,
        ]);
        $other->update(['owner_id' => $sophie->id]);
        Client::factory()->count(5)->create(['company_id' => $other->id]);
    }

    /**
     * Crée quelques devis (statuts variés) et transforme l'un d'eux en facture
     * partiellement payée, pour illustrer le flux Phase 2.
     */
    private function seedQuotesAndInvoices(int $companyId, $clients): void
    {
        $catalog = [
            ['main_oeuvre', 'Main d\'œuvre (taux horaire)', 8, 45, 20],
            ['materiel', 'Tuyau PVC Ø100 (mètre)', 12, 6.5, 20],
            ['materiel', 'Robinet thermostatique', 2, 89, 20],
            ['deplacement', 'Déplacement forfait', 1, 35, 20],
        ];
        $statuses = ['draft', 'sent', 'accepted', 'accepted', 'refused'];

        foreach ($statuses as $i => $status) {
            $client = $clients[$i % $clients->count()];

            $quote = Quote::create([
                'company_id'  => $companyId,
                'client_id'   => $client->id,
                'number'      => Quote::nextNumber($companyId),
                'status'      => $status,
                'title'       => 'Intervention plomberie',
                'issue_date'  => now()->subDays(($i + 1) * 4)->toDateString(),
                'valid_until' => now()->addDays(20)->toDateString(),
            ]);

            foreach (array_slice($catalog, 0, rand(2, 4)) as $pos => [$type, $desc, $qty, $price, $tva]) {
                $quote->lines()->create([
                    'type' => $type, 'description' => $desc, 'quantity' => $qty,
                    'unit_price' => $price, 'tax_rate' => $tva, 'position' => $pos,
                ]);
            }
            $quote->load('lines');
            $quote->recalculateTotals();
        }

        // Une facture issue du premier devis accepté, payée à moitié.
        $accepted = Quote::withoutGlobalScopes()->where('company_id', $companyId)->where('status', 'accepted')->first();
        if ($accepted) {
            $accepted->load('lines');
            $invoice = Invoice::create([
                'company_id' => $companyId,
                'client_id'  => $accepted->client_id,
                'quote_id'   => $accepted->id,
                'number'     => Invoice::nextNumber($companyId),
                'status'     => 'unpaid',
                'title'      => $accepted->title,
                'issue_date' => now()->subDays(5)->toDateString(),
                'due_date'   => now()->addDays(25)->toDateString(),
            ]);
            foreach ($accepted->lines as $line) {
                $invoice->lines()->create($line->only(['type', 'description', 'quantity', 'unit_price', 'tax_rate', 'position']));
            }
            $invoice->load('lines');
            $invoice->recalculateTotals();

            $invoice->payments()->create([
                'company_id' => $companyId,
                'amount'     => round((float) $invoice->total_ttc / 2, 2),
                'paid_at'    => now()->subDays(2)->toDateString(),
                'method'     => 'virement',
                'note'       => 'Acompte 50 %',
            ]);
            $invoice->refreshPaymentStatus();
        }
    }

    /**
     * Produits (dont stock faible), chantiers et interventions de démonstration.
     */
    private function seedPhase3(int $companyId, $clients, User $technician): void
    {
        // Stock (avec fournisseur + quantité de réappro pour la commande automatique)
        $products = [
            ['Tuyau PVC Ø100', 'PVC-100', 'm', 2.10, 6.50, 3, 10, 50],   // stock faible
            ['Coude cuivre 16mm', 'CU-16', 'u', 0.80, 2.40, 120, 20, 100],
            ['Robinet thermostatique', 'ROB-TH', 'u', 45, 89, 4, 5, 10], // stock faible
            ['Joint silicone', 'JNT-SIL', 'u', 1.20, 4.90, 60, 15, 50],
            ['Chauffe-eau 200L', 'CE-200', 'u', 280, 590, 2, 1, 5],
        ];
        foreach ($products as [$name, $ref, $unit, $pa, $pv, $stock, $min, $reorder]) {
            Product::create([
                'company_id' => $companyId, 'name' => $name, 'reference' => $ref, 'unit' => $unit,
                'purchase_price' => $pa, 'sale_price' => $pv, 'stock' => $stock, 'min_stock' => $min,
                'supplier_name' => 'Sanitaire Pro', 'supplier_email' => 'commande@sanitaire-pro.fr',
                'reorder_quantity' => $reorder,
            ]);
        }

        // Chantiers
        $data = [
            ['Rénovation salle de bain', 'in_progress', 70, 8500],
            ['Installation chaudière', 'planned', 0, 4200],
            ['Réfection plomberie immeuble', 'in_progress', 35, 23000],
            ['Dépannage fuite', 'done', 100, 450],
        ];
        $projects = [];
        foreach ($data as $i => [$name, $status, $progress, $budget]) {
            $project = Project::create([
                'company_id' => $companyId,
                'client_id'  => $clients[$i % $clients->count()]->id,
                'name'       => $name, 'status' => $status, 'progress' => $progress, 'budget' => $budget,
                'city'       => 'Lyon',
                'start_date' => now()->subDays(($i + 1) * 7)->toDateString(),
                'end_date'   => now()->addDays(($i + 1) * 10)->toDateString(),
            ]);
            $project->comments()->create([
                'company_id' => $companyId, 'user_id' => $technician->id,
                'body'       => 'Chantier démarré, matériel livré sur place.',
            ]);
            $projects[] = $project;
        }

        // Dépenses récentes
        $expenses = [
            ['carburant', 'Plein gasoil camionnette', 92.40, 3],
            ['materiel', 'Achat coudes cuivre', 156.00, 1],
            ['fournitures', 'Visserie et consommables', 48.75, 8],
            ['carburant', 'Plein gasoil', 88.10, 18],
            ['sous_traitance', 'Électricien partenaire', 420.00, 12],
        ];
        foreach ($expenses as [$cat, $label, $amount, $daysAgo]) {
            Expense::create([
                'company_id' => $companyId,
                'project_id' => $projects[array_rand($projects)]->id,
                'category'   => $cat,
                'label'      => $label,
                'amount'     => $amount,
                'spent_at'   => now()->subDays($daysAgo)->toDateString(),
                'supplier'   => 'Fournisseur démo',
            ]);
        }

        // Planning : quelques interventions cette semaine
        $slots = [
            ['Pose robinetterie', 0, 9, 11],
            ['Visite technique', 1, 14, 15],
            ['Dépannage chauffe-eau', 2, 8, 10],
            ['Réception chantier', 3, 16, 17],
        ];
        foreach ($slots as $i => [$title, $dayOffset, $h1, $h2]) {
            Intervention::create([
                'company_id'    => $companyId,
                'client_id'     => $clients[$i % $clients->count()]->id,
                'project_id'    => $projects[$i % count($projects)]->id,
                'technician_id' => $technician->id,
                'title'         => $title,
                'status'        => 'planned',
                'start_at'      => now()->addDays($dayOffset)->setTime($h1, 0),
                'end_at'        => now()->addDays($dayOffset)->setTime($h2, 0),
            ]);
        }
    }
}
