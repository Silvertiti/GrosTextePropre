<?php

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use DI\Container;

require_once __DIR__ . '/vendor/autoload.php';

$settings = require __DIR__ . '/settings.php';

$container = new Container();

// Injecter les settings dans le container
$container->set('settings', $settings['settings']);

// Configurer Doctrine
$container->set(EntityManager::class, function () use ($settings) {
    $doctrineSettings = $settings['settings']['doctrine'];

    $cache = $doctrineSettings['dev_mode']
        ? new ArrayAdapter()
        : new FilesystemAdapter(directory: $doctrineSettings['cache_dir']);

    // Créer la configuration Doctrine
    $config = ORMSetup::createAttributeMetadataConfiguration(
        $doctrineSettings['metadata_dirs'],
        $doctrineSettings['dev_mode'],
        null,
        $cache
    );

    // Connexion
    $connection = DriverManager::getConnection(
        $doctrineSettings['connection'],
        $config 
    );

    return new EntityManager($connection, $config);
});

return $container;
