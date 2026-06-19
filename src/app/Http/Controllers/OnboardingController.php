<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Premier paramétrage après l'inscription : l'owner choisit les modules à
 * activer pour son entreprise. À la création, tous les modules du socle sont
 * actifs par défaut ; cet écran permet d'en retirer et d'ajouter des modules
 * métiers.
 */
class OnboardingController extends Controller
{
    public function show(Request $request): View
    {
        $company = $request->user()->company;

        $core = collect(config('modules'))
            ->reject(fn ($m) => $m['mandatory'] ?? false)
            ->map(fn ($m, $key) => $this->card($key, $m, 'Socle', true, $company->isModuleEnabled($key)));

        $sector = collect(config('sector_modules'))
            ->filter(fn ($m) => $m['available'] ?? false)
            ->map(fn ($m, $key) => $this->card($key, $m, 'Métier', false, $company->hasModule($key)));

        $modules = $core->values()->concat($sector->values())->groupBy('group');

        return view('onboarding.index', compact('modules'));
    }

    public function store(Request $request): RedirectResponse
    {
        $company = $request->user()->company;

        $chosen = (array) $request->input('modules', []);

        // Tous les modules activables (socle non obligatoire + verticaux disponibles).
        $toggleable = collect(config('modules'))
            ->reject(fn ($m) => $m['mandatory'] ?? false)
            ->keys()
            ->merge(collect(config('sector_modules'))->filter(fn ($m) => $m['available'] ?? false)->keys());

        foreach ($toggleable as $key) {
            if (in_array($key, $chosen, true)) {
                $company->enableModule($key);
            } else {
                $company->disableModule($key);
            }
        }

        return redirect()->route('dashboard')->with('status', 'Modules configurés. Bienvenue sur ArtisanFlow !');
    }

    /** @return array<string, mixed> */
    private function card(string $key, array $m, string $group, bool $core, bool $active): array
    {
        return [
            'key'         => $key,
            'group'       => $group,
            'label'       => $m['label'],
            'description' => $core ? $m['description'] : ($m['feature'] ?? $m['description']),
            'icon'        => $m['icon'],
            'color'       => $m['color'],
            'checked'     => $active,
        ];
    }
}
