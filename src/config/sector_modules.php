<?php

/*
|--------------------------------------------------------------------------
| Modules métiers optionnels (verticaux)
|--------------------------------------------------------------------------
|
| Catalogue des modules activables par entreprise (en plus du socle).
| `available` = true → activable ; false → « Prochainement » (vitrine).
| `feature` décrit ce que le module débloque une fois activé.
|
*/

return [

    'batiment' => [
        'label'       => 'Bâtiment',
        'sector'      => 'Électricien · Plombier · Peintre',
        'description' => 'Bibliothèque de prestations par métier, insérables en un clic dans vos devis.',
        'feature'     => 'Catalogue de prestations',
        'icon'        => 'bi-bricks',
        'color'       => '#d97706',
        'price'       => '9 €/mois',
        'available'   => true,
        'route'       => 'catalog.index', // ouvert depuis le tableau de bord une fois activé
    ],

    'opticien' => [
        'label'       => 'Opticien',
        'sector'      => 'Magasins d\'optique',
        'description' => 'Ordonnances (OD/OG, écart pupillaire), suivi visuel des clients.',
        'feature'     => 'Ordonnances optiques',
        'icon'        => 'bi-eyeglasses',
        'color'       => '#0ea5e9',
        'price'       => '19 €/mois',
        'available'   => true,
        'route'       => 'prescriptions.index',
    ],

    'immobilier' => [
        'label'       => 'Immobilier',
        'sector'      => 'Agences & gestion locative',
        'description' => 'Catalogue de biens (vente/location), DPE, statuts et mandants.',
        'feature'     => 'Gestion des biens',
        'icon'        => 'bi-house-door',
        'color'       => '#7c3aed',
        'price'       => '29 €/mois',
        'available'   => true,
        'route'       => 'properties.index',
    ],

    'concessionnaire' => [
        'label'       => 'Concessionnaire',
        'sector'      => 'Vente de véhicules',
        'description' => 'Parc véhicules (VIN, immat., km), stock neuf/occasion et statuts.',
        'feature'     => 'Parc véhicules',
        'icon'        => 'bi-car-front',
        'color'       => '#dc2626',
        'price'       => '29 €/mois',
        'available'   => true,
        'route'       => 'vehicles.index',
    ],

];
