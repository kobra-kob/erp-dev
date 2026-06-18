<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Quote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /** Redirige la racine vers le tableau de bord ou la connexion (route cacheable). */
    public function root()
    {
        return redirect()->route(Auth::check() ? 'dashboard' : 'login');
    }

    public function index(): View
    {
        $user = Auth::user();

        $modules = $this->resolveModules($user);

        $unpaid = (float) Invoice::whereIn('status', ['unpaid', 'partial'])
            ->selectRaw('COALESCE(SUM(total_ttc - paid_amount), 0) AS due')
            ->value('due');

        $kpis = [
            'clients'  => Client::count(),
            'quotes'   => [
                'value' => (string) Quote::whereIn('status', ['draft', 'sent'])->count(),
                'label' => 'en cours',
                'soon'  => false,
            ],
            'invoices' => [
                'value' => number_format($unpaid, 0, ',', ' ') . ' €',
                'label' => 'à encaisser',
                'soon'  => false,
            ],
        ];

        return view('dashboard.index', compact('modules', 'kpis'));
    }

    /**
     * Persiste l'organisation du launcher (ordre, favoris, masqués).
     */
    public function updatePreferences(Request $request): JsonResponse
    {
        $keys = array_keys(config('modules'));

        $data = $request->validate([
            'order'     => ['array'],
            'order.*'   => ['string', 'in:' . implode(',', $keys)],
            'hidden'    => ['array'],
            'hidden.*'  => ['string', 'in:' . implode(',', $keys)],
            'favorites' => ['array'],
            'favorites.*' => ['string', 'in:' . implode(',', $keys)],
        ]);

        $user = $request->user();
        $user->module_preferences = [
            'order'     => $data['order'] ?? [],
            'hidden'    => $data['hidden'] ?? [],
            'favorites' => $data['favorites'] ?? [],
        ];
        $user->save();

        return response()->json(['status' => 'ok']);
    }

    /**
     * Construit la liste des modules visibles par l'utilisateur, en appliquant
     * ses préférences (rôle, ordre personnalisé, favoris, masquage).
     *
     * @return array<int, array<string, mixed>>
     */
    private function resolveModules($user): array
    {
        $prefs     = $user->module_preferences ?? [];
        $order     = $prefs['order'] ?? [];
        $hidden    = $prefs['hidden'] ?? [];
        $favorites = $prefs['favorites'] ?? [];

        // Modules du socle accessibles selon le rôle (les Paramètres ne sont pas
        // une « app » : accessibles via le menu utilisateur, pas le launcher).
        $core = collect(config('modules'))
            ->reject(fn ($m, $key) => $key === 'settings')
            ->filter(fn ($m, $key) => $user->canAccessModule($key))
            ->map(fn ($m, $key) => array_merge($m, ['key' => $key]));

        // Modules optionnels (verticaux) ACTIVÉS pour l'entreprise → s'ajoutent au launcher.
        $company = $user->company;
        $sector = collect(config('sector_modules'))
            ->filter(fn ($m, $key) => ($m['available'] ?? false) && ($m['route'] ?? null) && $company?->hasModule($key))
            ->map(fn ($m, $key) => [
                'key'         => $key,
                'label'       => $m['label'],
                'description' => $m['feature'] ?? $m['description'],
                'icon'        => $m['icon'],
                'color'       => $m['color'],
                'route'       => $m['route'],
                'available'   => true,
            ]);

        $modules = $core->values()->concat($sector->values())
            ->map(fn ($m) => array_merge($m, [
                'is_hidden'   => in_array($m['key'], $hidden, true),
                'is_favorite' => in_array($m['key'], $favorites, true),
            ]));

        // Tri : ordre personnalisé d'abord, puis ordre par défaut du fichier de config.
        return $modules->sortBy(function ($m) use ($order) {
            $pos = array_search($m['key'], $order, true);
            return $pos === false ? PHP_INT_MAX : $pos;
        })->values()->all();
    }
}
