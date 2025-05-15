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
        'Total Tables' => 'Tables totales',
        'Missing in DB1' => 'Manquante dans BD1',
        'Missing in DB2' => 'Manquante dans BD2',
        'Tables Comparison' => 'Comparaison des tables',
        'Invalid request' => 'Demande invalide',
        'Table Comparison' => 'Comparaison de la table',
        'Missing Tables in DB1' => 'Tables manquantes dans BD1',
        'Missing Tables in DB2' => 'Tables manquantes dans BD2',
        'Structure Mismatches' => 'Incompatibilités de structure',
        'Structure Mismatch' => 'Structure Incompatible',
        'Data Mismatches' => 'Incompatibilités de données',
        'Indexes DB1' => 'Indexes BD1',
        'Indexes DB2' => 'Indexes BD2',
        'No Mismatches Found' => 'Aucune incompatibilité trouvée',
        'Tables are identical' => 'Les tables sont identiques',
        'Table Structure' => 'Structure de la table',
        'Table Data' => 'Données de la table',
        'Table Name' => 'Nom de la table',
        'Column Name' => 'Nom de la colonne',
        'Column Type' => 'Type de colonne',
        'Column Length' => 'Longueur de la colonne',
        'View' => 'Voir',
        'Index' => 'Index',
        'Summary' => 'Résumé',
        'Detailed' => 'Détaillé',
        'Details' => 'Détails',
        'compare with' => 'comparer avec',
        'Identical Tables' => 'Tables identiques',
        'Identical' => 'Identique',
        'Different' => 'Différent',
        'Differences' => 'Différences',
        'Comparison Summary' => 'Résumé de la comparaison',
        'Comparison Results' => 'Résultats de la comparaison',
        'No differences found' => 'Aucune différence trouvée',
    ],
    "es"=> [
        'Database Comparison Tool' => 'Herramienta de Comparación de Bases de Datos',
        'Login' => 'Iniciar sesión',
        'Host' => 'Anfitrión',
        'Username' => 'Nombre de usuario',
        'Password' => 'Contraseña',
        "Connected as: {username}" => "Conectado como: {username}",
        'Select Databases to Compare' => 'Seleccionar Bases de Datos para Comparar',
        'Database 1' => 'Base de Datos 1',
        'Database 2' => 'Base de Datos 2',
        'Select Database 1' => 'Seleccionar Base de Datos 1',
        'Select Database 2' => 'Seleccionar Base de Datos 2',
        'Remember username' => 'Recordar nombre de usuario',
        'Connect' => 'Conectar',
        "Please select two databases to compare" => "Por favor seleccione dos bases de datos para comparar",
        'Connection failed. Please check your credentials.' => 'Conexión fallida. Por favor verifique sus credenciales.',
        'Total Tables' => 'Tablas totales',
        'Missing in DB1' => 'Faltan en BD1',
        'Missing in DB2' => 'Faltan en BD2',
        'Tables Comparison' => 'Comparación de Tablas',
        'Invalid request' => 'Solicitud inválida',
        'Table Comparison' => 'Comparación de la tabla',
        'Missing Tables in DB1' => 'Tablas faltantes en BD1',
        'Missing Tables in DB2' => 'Tablas faltantes en BD2',
        'Structure Mismatches' => 'Incompatibilidades de estructura',
        'Structure Mismatch' => 'Estructura Incompatible',
        'Data Mismatches' => 'Incompatibilidades de datos',
        'Indexes DB1' => 'Índices BD1',
        'Indexes DB2' => 'Índices BD2',
        'No Mismatches Found' => 'No se encontraron incompatibilidades',
        'Tables are identical' => 'Las tablas son idénticas',
        'Table Structure' => 'Estructura de la tabla',
        'Table Data' => 'Datos de la tabla',
        'Table Name' => 'Nombre de la tabla',
        'Column Name' => 'Nombre de la columna',
        'Column Type' => 'Tipo de columna',
        'Column Length' => 'Longitud de la columna',
        'View' => 'Ver',
        'Index' => 'Índice',
        'Summary' => 'Resumen',
        'Detailed' => 'Detallado',
        'Details' => 'Detalles',
        'compare with' => 'comparar con',
        'Identical Tables' => 'Tablas idénticas',
        'Identical' => 'Idéntico',
        'Different' => 'Diferente',
        'Differences' => 'Diferencias',
        'Comparison Summary' => 'Resumen de la comparación',
        'Comparison Results' => 'Resultados de la comparación',
        'No differences found' => 'No se encontraron diferencias',
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

