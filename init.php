<?php

$translations = [
    'fr' => [
        'Database Comparison Tool' => 'Outil Comparison Base de données',
        'Login' => 'Connexion',
        'Host' => 'Hôte',
        'Username' => 'Nom d\'utilisateur',
        'Password' => 'Mot de passe',
        'Remember username' => 'Se souvenir du nom d\'utilisateur',
        'Connect' => 'Se connecter',
        'Connection failed. Please check your credentials.' => 'Échec de la connexion. Veuillez vérifier vos identifiants.',
    ],
];

function T($key, $vars=[], $lang = 'fr') {
    global $translations;

    if (isset($translations[$lang][$key])) {
        $retval = $translations[$lang][$key];
    } else {
        $retval = $key; // Return the key if translation is not found
    }

    
    foreach ($vars as $var => $value) {
        $retval = str_replace('{' . $var . '}', $value, $retval);
    }

    return $retval;
}

