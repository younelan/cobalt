<?php

$translations = [
    'fr' => [
        'Database Comparison Tool' => 'Outil Comparaison Base de Données',
        'Login' => 'Connexion',
        'Host' => 'Hôte',
        'Username' => 'Nom d\'utilisateur',
        'Password' => 'Mot de passe',
        "Connected as: {username}" => "Connecté en tant que : {username}",
        'Select Databases to Compare' => 'Sélectionner les bases de données à comparer',
        'Database 1' => 'Base de données 1',
        'Database 2' => 'Base de données 2',
        'Select Database 1' => 'Sélectionner la base de données 1',
        'Select Database 2' => 'Sélectionner la base de données 2',
        'Remember username' => 'Se souvenir du nom d\'utilisateur',
        'Connect' => 'Se connecter',
        "Please select two databases to compare" => "Veuillez sélectionner deux bases de données à comparer",
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

