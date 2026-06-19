<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Catalogue des modules optionnels (verticaux) : l'administrateur active ou
 * désactive les modules de son entreprise à tout moment. La désactivation
 * conserve les données (réversible).
 */
class ModuleCatalogController extends Controller
{
    public function index(Request $request): View
    {
        $company = $request->user()->company;

        // Modules du socle (hors « settings » obligatoire) — toujours activables.
        $core = collect(config('modules'))
            ->reject(fn ($m) => $m['mandatory'] ?? false)
            ->map(fn ($m, $key) => [
                'key'         => $key,
                'group'       => 'Socle',
                'label'       => $m['label'],
                'description' => $m['description'],
                'icon'        => $m['icon'],
                'color'       => $m['color'],
                'sector'      => 'Module de base',
                'price'       => null,
                'available'   => true,
                'active'      => $company->isModuleEnabled($key),
            ]);

        // Modules métiers (verticaux).
        $sector = collect(config('sector_modules'))
            ->map(fn ($m, $key) => [
                'key'         => $key,
                'group'       => 'Métier',
                'label'       => $m['label'],
                'description' => $m['description'],
                'icon'        => $m['icon'],
                'color'       => $m['color'],
                'sector'      => $m['sector'] ?? null,
                'price'       => $m['price'] ?? null,
                'available'   => $m['available'] ?? false,
                'active'      => $company->hasModule($key),
            ]);

        $modules = $core->values()->concat($sector->values())->groupBy('group');

        return view('modules.catalog', compact('modules'));
    }

    public function toggle(Request $request, string $key): RedirectResponse
    {
        $core   = config("modules.$key");
        $sector = config("sector_modules.$key");
        $module = $core ?: $sector;

        abort_if(! $module, 404);
        // Un module obligatoire (Paramètres) ne peut pas être désactivé.
        abort_if($core && ($core['mandatory'] ?? false), 403);

        if ($sector && ! ($sector['available'] ?? false)) {
            return back()->withErrors(['module' => 'Ce module n\'est pas encore disponible.']);
        }

        $company = $request->user()->company;

        if ($company->hasModule($key)) {
            $company->disableModule($key);
            $status = "Module « {$module['label']} » désactivé (vos données sont conservées).";
        } else {
            $company->enableModule($key);
            $status = "Module « {$module['label']} » activé.";
        }

        return back()->with('status', $status);
    }
}
