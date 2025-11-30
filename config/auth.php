<?php

return [

    'defaults' => [
        'guard' => 'api', // ✅ Le guard par défaut
        'passwords' => 'users',
    ],

    'guards' => [
        'web' => [
            'driver' => 'session', // ✅ Le guard web (pour les vues classiques)
            'provider' => 'users',
        ],

        'api' => [
            'driver' => 'jwt', // ✅ C’est ce guard qu’utilise ton API
            'provider' => 'users',
            'hash' => false,
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\Utilisateur::class, // ✅ Ton modèle d’utilisateur
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,
];
