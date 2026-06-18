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

        $modules = collect(config('sector_modules'))
            ->map(fn ($m, $key) => $m + ['key' => $key, 'active' => $company->hasModule($key)]);

        return view('modules.catalog', compact('modules'));
    }

    public function toggle(Request $request, string $key): RedirectResponse
    {
        $module = config("sector_modules.$key");
        abort_if(! $module, 404);

        if (! ($module['available'] ?? false)) {
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
