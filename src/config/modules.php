<?php

/*
|--------------------------------------------------------------------------
| Modules de l'ERP (launcher style Odoo)
|--------------------------------------------------------------------------
|
| Chaque module est une « application » affichée sous forme de bloc sur le
| tableau de bord. `available` = false → bloc grisé « bientôt » (phases 2-4).
| `route` = nom de route Laravel à ouvrir au clic (null si indisponible).
| `roles` = rôles autorisés à voir/ouvrir le module.
| `icon` = classe Bootstrap Icons (https://icons.getbootstrap.com).
|
*/

return [

    'clients' => [
        'label'       => 'Clients / CRM',
        'description' => 'Gérez vos clients et leur historique.',
        'icon'        => 'bi-people-fill',
        'color'       => '#2563eb',
        'route'       => 'clients.index',
        'available'   => true,
        'roles'       => ['ADMIN', 'GERANT'],
    ],

    'quotes' => [
        'label'       => 'Devis',
        'description' => 'Créez et suivez vos devis.',
        'icon'        => 'bi-file-earmark-text-fill',
        'color'       => '#7c3aed',
        'route'       => 'quotes.index',
        'available'   => true,
        'roles'       => ['ADMIN', 'GERANT'],
    ],

    'invoices' => [
        'label'       => 'Factures',
        'description' => 'Facturation et paiements.',
        'icon'        => 'bi-receipt',
        'color'       => '#059669',
        'route'       => 'invoices.index',
        'available'   => true,
        'roles'       => ['ADMIN', 'GERANT'],
    ],

    'planning' => [
        'label'       => 'Planning',
        'description' => 'Interventions et rendez-vous.',
        'icon'        => 'bi-calendar-week-fill',
        'color'       => '#ea580c',
        'route'       => 'interventions.index',
        'available'   => true,
        'roles'       => ['ADMIN', 'GERANT', 'EMPLOYE'],
    ],

    'projects' => [
        'label'       => 'Chantiers',
        'description' => 'Suivi des chantiers et avancement.',
        'icon'        => 'bi-bricks',
        'color'       => '#d97706',
        'route'       => 'projects.index',
        'available'   => true,
        'roles'       => ['ADMIN', 'GERANT', 'EMPLOYE'],
    ],

    'stock' => [
        'label'       => 'Stock',
        'description' => 'Produits, matériel et alertes.',
        'icon'        => 'bi-box-seam-fill',
        'color'       => '#0891b2',
        'route'       => 'products.index',
        'available'   => true,
        'roles'       => ['ADMIN', 'GERANT'],
    ],

    'expenses' => [
        'label'       => 'Dépenses',
        'description' => 'Carburant, fournitures, matériel.',
        'icon'        => 'bi-cash-coin',
        'color'       => '#dc2626',
        'route'       => 'expenses.index',
        'available'   => true,
        'roles'       => ['ADMIN', 'GERANT'],
    ],

    'employees' => [
        'label'       => 'Employés',
        'description' => 'Équipe, compétences, disponibilités.',
        'icon'        => 'bi-person-badge-fill',
        'color'       => '#4f46e5',
        'route'       => 'employees.index',
        'available'   => true,
        'roles'       => ['ADMIN'],
    ],

    'documents' => [
        'label'       => 'Documents',
        'description' => 'Contrats, photos, PDF.',
        'icon'        => 'bi-folder-fill',
        'color'       => '#0d9488',
        'route'       => 'documents.index',
        'available'   => true,
        'roles'       => ['ADMIN', 'GERANT', 'EMPLOYE'],
    ],

    'leaves' => [
        'label'       => 'Congés',
        'description' => 'Demandes et suivi des congés.',
        'icon'        => 'bi-umbrella-fill',
        'color'       => '#14b8a6',
        'route'       => 'leaves.index',
        'available'   => true,
        'roles'       => ['ADMIN', 'GERANT', 'EMPLOYE'],
    ],

    'accounting' => [
        'label'       => 'Comptabilité',
        'description' => 'Écritures, banque, balance, FEC.',
        'icon'        => 'bi-calculator-fill',
        'color'       => '#1e3a8a',
        'route'       => 'accounting.index',
        'available'   => true,
        'roles'       => ['ADMIN', 'GERANT'],
    ],

    'assistant' => [
        'label'       => 'Assistant IA',
        'description' => 'Posez vos questions métier.',
        'icon'        => 'bi-robot',
        'color'       => '#0ea5e9',
        'route'       => 'assistant.index',
        'available'   => true,
        'roles'       => ['ADMIN', 'GERANT'],
    ],

    'statistics' => [
        'label'       => 'Statistiques',
        'description' => 'Chiffre d\'affaires et indicateurs.',
        'icon'        => 'bi-graph-up-arrow',
        'color'       => '#9333ea',
        'route'       => 'statistics.index',
        'available'   => true,
        'roles'       => ['ADMIN', 'GERANT'],
    ],

    'settings' => [
        'label'       => 'Paramètres',
        'description' => 'Entreprise, sécurité, 2FA.',
        'icon'        => 'bi-gear-fill',
        'color'       => '#475569',
        'route'       => 'settings.index',
        'available'   => true,
        'roles'       => ['ADMIN', 'GERANT', 'EMPLOYE'],
    ],

];
