<?php

return [

    // ✅ Υποχρεωτικό στο v4
    'auth_provider_model' => 'App\\Models\\User',

    // ✅ Super admin format (όχι string)
    'super_admin' => [
        'enabled' => true,
        'name' => 'Administrator',
    ],

    // ✅ Θέλουμε να ΠΑΡΑΜΕΙΝΕΙ το pattern μας: view_any_contract
    'permissions' => [
        'separator' => '_',   // default στο v4 είναι ':'
        'case'      => 'snake', // default στο v4 είναι 'pascal'
        'generate'  => true,  // να παράγει keys για Resources
    ],

    // ✅ Policies: έχουμε custom (π.χ. UserPolicy). Δεν θες να σου γράφει καινούργιες.
    'policies' => [
        'path'   => app_path('Policies'),
        'merge'  => true,
        'generate' => false, // <— σημαντικό για εμάς
        'methods' => [
            'viewAny','view','create','update','delete','restore',
            'forceDelete','forceDeleteAny','restoreAny','replicate','reorder',
        ],
        'single_parameter_methods' => [
            'viewAny','create','deleteAny','forceDeleteAny','restoreAny','reorder',
        ],
    ],

    // ✅ Θέλουμε permissions μόνο για Resources (CRUD). Pages/Widgets off.
    'resources' => [
        'subject' => 'model', // παίρνει το Model name (σωστό για το pattern μας)
        'manage'  => [
            // μπορείς να ορίσεις ειδικές περιπτώσεις resource->methods αν χρειαστεί
        ],
        'exclude' => [
            // resources που δεν θες να αγγίζει (αν υπάρξουν)
        ],
    ],

    'pages' => [
        'subject' => 'class',
        'prefix'  => 'view',
        'exclude' => [
            \Filament\Pages\Dashboard::class,
        ],
    ],

    'widgets' => [
        'subject' => 'class',
        'prefix'  => 'view',
        'exclude' => [
            \Filament\Widgets\AccountWidget::class,
            \Filament\Widgets\FilamentInfoWidget::class,
        ],
    ],

    'shield_resource' => [
        'navigation' => [
            'register' => true, // 👈
            'group' => 'Σύστημα',
            'sort' => 99,
        ],
    ],

    // (προαιρετικά)
    'localization' => [
        'enabled' => false,
        'key' => 'filament-shield::filament-shield', // άστο όπως είναι αν δεν μεταφράζεις labels
    ],
];

