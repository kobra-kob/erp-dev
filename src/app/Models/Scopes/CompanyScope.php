<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

/**
 * Global scope d'isolation multi-entreprises.
 *
 * Tant qu'un utilisateur est authentifié, toutes les requêtes sur les modèles
 * « tenant » sont automatiquement filtrées sur sa company_id. Les données d'une
 * entreprise ne peuvent donc jamais fuiter vers une autre.
 */
class CompanyScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (Auth::check() && ($companyId = Auth::user()->company_id)) {
            $builder->where($model->getTable() . '.company_id', $companyId);
        }
    }
}
