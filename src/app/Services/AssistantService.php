<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Project;
use App\Models\Quote;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * « Assistant Artisan ».
 *
 * Toujours alimenté par les données réelles de l'entreprise (calculées en base,
 * donc gratuites et exactes). Si une clé d'API compatible OpenAI est configurée,
 * un LLM formule la réponse à partir de ce contexte ; sinon un moteur local
 * répond aux questions courantes par mots-clés.
 */
class AssistantService
{
    public function __construct(private readonly StockReplenishmentService $replenisher) {}

    public function isAiEnabled(): bool
    {
        return filled(config('assistant.api_key'));
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $history
     * @return array{answer: string, source: string}
     */
    public function ask(string $question, array $history = []): array
    {
        // Action déterministe : commander le réapprovisionnement (avant toute IA).
        if ($this->isReorderIntent($question)) {
            return ['answer' => $this->performReorder(), 'source' => 'action'];
        }

        $context = $this->buildContext();

        if ($this->isAiEnabled()) {
            $answer = $this->askLlm($question, $context, $history);
            if ($answer !== null) {
                return ['answer' => $answer, 'source' => 'ia'];
            }
        }

        return ['answer' => $this->localAnswer($question, $context), 'source' => 'local'];
    }

    /**
     * Données métier de l'entreprise courante (scopées automatiquement).
     *
     * @return array<string, mixed>
     */
    public function buildContext(): array
    {
        $unpaid = Invoice::whereIn('status', ['unpaid', 'partial'])->with('client')->get();
        $lowStock = Product::whereColumn('stock', '<=', 'min_stock')->get();

        return [
            'clients'         => Client::count(),
            'quotes_pending'  => Quote::whereIn('status', ['draft', 'sent'])->count(),
            'quotes_accepted' => Quote::where('status', 'accepted')->count(),
            'projects_active' => Project::where('status', 'in_progress')->count(),
            'revenue_year'    => round((float) Invoice::whereYear('issue_date', now()->year)->sum('paid_amount'), 2),
            'invoiced_year'   => round((float) Invoice::whereYear('issue_date', now()->year)->sum('total_ttc'), 2),
            'unpaid_total'    => round($unpaid->sum(fn ($i) => $i->remainingAmount()), 2),
            'unpaid_count'    => $unpaid->count(),
            'unpaid_list'     => $unpaid->take(15)->map(fn ($i) => [
                'numero'      => $i->number,
                'client'      => $i->client?->name,
                'restant_du'  => $i->remainingAmount(),
                'echeance'    => optional($i->due_date)->format('d/m/Y'),
                'en_retard'   => $i->isOverdue(),
            ])->all(),
            'low_stock_list'  => $lowStock->take(15)->map(fn ($p) => [
                'produit'      => $p->name,
                'stock'        => (float) $p->stock,
                'minimum'      => (float) $p->min_stock,
                'fournisseur'  => $p->supplier_name,
                'commande_qte' => $p->orderQuantity(),
                'commandable'  => $p->canReorder(),
            ])->all(),
        ];
    }

    private function isReorderIntent(string $question): bool
    {
        $q = Str::lower($question);

        if (Str::contains($q, ['réappro', 'reappro', 'approvision', 'réassort', 'reassort'])) {
            return true;
        }

        // « commande/commander » au sens stock/fournisseur (pas un devis client).
        return Str::contains($q, ['command'])
            && Str::contains($q, ['stock', 'produit', 'fournisseur']);
    }

    /** Passe les commandes de réapprovisionnement pour tous les produits éligibles. */
    private function performReorder(): string
    {
        $orders = $this->replenisher->replenishLowStock('ai');

        if ($orders->isEmpty()) {
            return "Aucune commande à passer : soit aucun produit n'est en stock faible, "
                . "soit ceux qui le sont n'ont pas d'e-mail fournisseur renseigné.";
        }

        $lines = $orders->map(fn ($o) =>
            "• {$o->product->name} : " . rtrim(rtrim(number_format($o->quantity, 2, ',', ' '), '0'), ',')
            . " {$o->product->unit} commandé(s) chez " . ($o->supplier_name ?: 'fournisseur')
        )->implode("\n");

        return "✅ J'ai passé {$orders->count()} commande(s) de réapprovisionnement. "
            . "Les fournisseurs ont été notifiés par e-mail et le stock a été mis à jour :\n\n" . $lines;
    }

    /**
     * Appel d'un modèle compatible OpenAI (/v1/chat/completions).
     *
     * @param  array<int, array{role: string, content: string}>  $history
     */
    private function askLlm(string $question, array $context, array $history): ?string
    {
        $system = "Tu es « Assistant Artisan », l'assistant d'un logiciel de gestion (ERP) pour artisans. "
            . "Réponds en français, de façon concise et professionnelle. "
            . "Utilise UNIQUEMENT les données de l'entreprise fournies ci-dessous (ne les invente pas). "
            . "Les montants sont en euros. Si l'information n'est pas dans les données, dis-le simplement.\n\n"
            . "DONNÉES DE L'ENTREPRISE (JSON) :\n" . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        $messages = array_merge(
            [['role' => 'system', 'content' => $system]],
            array_slice($history, -6),
            [['role' => 'user', 'content' => $question]],
        );

        try {
            $response = Http::baseUrl(rtrim(config('assistant.base_url'), '/'))
                ->withToken(config('assistant.api_key'))
                ->timeout(config('assistant.timeout'))
                ->post('/chat/completions', [
                    'model'       => config('assistant.model'),
                    'messages'    => $messages,
                    'temperature' => 0.3,
                    'max_tokens'  => 600,
                ]);

            if ($response->successful()) {
                $content = $response->json('choices.0.message.content');

                return $content ? trim($content) : null;
            }

            Log::warning('Assistant LLM error', ['status' => $response->status(), 'body' => $response->body()]);
        } catch (\Throwable $e) {
            Log::warning('Assistant LLM exception', ['message' => $e->getMessage()]);
        }

        return null; // bascule sur la réponse locale
    }

    /**
     * Moteur de réponse local (sans IA) basé sur des mots-clés.
     *
     * @param  array<string, mixed>  $ctx
     */
    private function localAnswer(string $question, array $ctx): string
    {
        $q = mb_strtolower($question);
        $has = fn (array $words) => collect($words)->contains(fn ($w) => str_contains($q, $w));

        if ($has(['pay', 'impay', 'doiv', 'dû', 'du ', 'relanc', 'créance', 'creance'])) {
            if ($ctx['unpaid_count'] === 0) {
                return "Aucune facture impayée 🎉 Toutes vos factures sont réglées.";
            }
            $lines = collect($ctx['unpaid_list'])->map(fn ($i) =>
                "• {$i['numero']} — {$i['client']} : " . $this->eur($i['restant_du'])
                . ($i['en_retard'] ? ' ⚠️ en retard' : '')
            )->implode("\n");

            return "{$ctx['unpaid_count']} facture(s) impayée(s), total restant dû : "
                . $this->eur($ctx['unpaid_total']) . ".\n\n" . $lines;
        }

        if ($has(['chiffre', "ca ", 'revenu', 'encaiss', 'gagné', 'rentr'])) {
            return "Cette année : " . $this->eur($ctx['revenue_year']) . " encaissés "
                . "sur " . $this->eur($ctx['invoiced_year']) . " facturés.";
        }

        if ($has(['stock', 'rupture', 'alerte', 'réassort', 'commander'])) {
            if (empty($ctx['low_stock_list'])) {
                return "Aucun produit en stock faible. ✅";
            }
            $lines = collect($ctx['low_stock_list'])->map(function ($p) {
                $base = "• {$p['produit']} : {$p['stock']} (min. {$p['minimum']})";
                if ($p['commandable']) {
                    return $base . " → commander {$p['commande_qte']} chez {$p['fournisseur']}";
                }
                return $base . " ⚠️ pas d'e-mail fournisseur";
            })->implode("\n");

            $commandables = collect($ctx['low_stock_list'])->where('commandable', true)->count();
            $suffix = $commandables > 0
                ? "\n\nJe peux passer les commandes pour vous : répondez « commande le stock »."
                : '';

            return count($ctx['low_stock_list']) . " produit(s) à réapprovisionner :\n\n" . $lines . $suffix;
        }

        if ($has(['devis'])) {
            return "{$ctx['quotes_pending']} devis en cours (brouillon/envoyé) et "
                . "{$ctx['quotes_accepted']} devis accepté(s).";
        }

        if ($has(['chantier', 'projet'])) {
            return "{$ctx['projects_active']} chantier(s) en cours.";
        }

        if ($has(['client'])) {
            return "Vous avez {$ctx['clients']} client(s) enregistré(s).";
        }

        return "Je peux vous renseigner sur : les factures impayées, le chiffre d'affaires, "
            . "les devis en cours, l'état du stock, les chantiers et le nombre de clients. "
            . "Posez-moi par exemple : « Quels clients doivent payer ? »";
    }

    private function eur(float $n): string
    {
        return number_format($n, 2, ',', ' ') . ' €';
    }
}
