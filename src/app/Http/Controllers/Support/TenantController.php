<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\SupportAuditLog;
use Illuminate\View\View;

/**
 * Vue d'ensemble et détail des tenants depuis la console de support.
 *
 * Les modèles « tenant » portent le CompanyScope, mais celui-ci ne s'applique
 * qu'au guard « web » : sur le conteneur support (guard « support »), toutes les
 * entreprises sont donc visibles sans avoir à retirer le scope manuellement.
 */
class TenantController extends Controller
{
    /** Délai en deçà duquel un tenant est considéré « en ligne ». */
    private const ONLINE_WINDOW_MINUTES = 5;

    public function index(): View
    {
        $companies = Company::query()
            ->withCount(['users', 'clients'])
            ->withMax('users as last_seen_at', 'last_seen_at')
            ->with('owner')
            ->orderByDesc('last_seen_at')
            ->orderBy('id')
            ->paginate(20);

        return view('support.tenants.index', [
            'companies'     => $companies,
            'onlineWindow'  => self::ONLINE_WINDOW_MINUTES,
            'totalTenants'  => Company::count(),
            'onlineTenants' => Company::whereHas('users', fn ($q) => $q->where('last_seen_at', '>=', now()->subMinutes(self::ONLINE_WINDOW_MINUTES)))->count(),
        ]);
    }

    public function show(Company $company): View
    {
        $company->loadCount(['users', 'clients']);

        $audits = SupportAuditLog::query()
            ->where('company_id', $company->id)
            ->with('supportUser')
            ->latest('created_at')
            ->limit(50)
            ->get();

        return view('support.tenants.show', [
            'company'      => $company,
            'users'        => $company->users()->orderBy('id')->get(),
            'modules'      => $company->modules()->orderBy('module_key')->get(),
            'audits'       => $audits,
            'onlineWindow' => self::ONLINE_WINDOW_MINUTES,
        ]);
    }
}
