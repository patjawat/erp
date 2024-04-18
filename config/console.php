<?php

$params = require __DIR__ . '/params.php';
$modules = require __DIR__ . '/add_modules.php';
$db = require __DIR__ . '/db.php';
$db2 = require __DIR__ . '/db2.php';

$config = [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'app\commands',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
        '@tests' => '@app/tests',
    ],
    'components' => [
        'image' => [  
            'class' => 'yii\image\ImageDriver',
            'driver' => 'GD',  //GD or Imagick
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'db2' => $db2,
        'authManager' => [
            // 'class' => 'dektrium\rbac\components\DbManager',
            'class'        => 'yii\rbac\DbManager',
        ],
        // 'backup' => [
        //     'class' => 'amoracr\backup\Backup',
        //     // Path for storing the backups
        //     'backupDir' => '@app/backups',
        //     // Directories that will be added to backup
        //     'databases' => ['db'],
            
            
        //     'compression' => 'zip',
        
        //     'directories' => [
        //         'images'    => '@app/web/images',
        //         //'uploads'   => '@app/web/uploads',
        //     //    'immobili'  => '@webroot/immobili_root',
        //         //'clienti'   => '@webroot/clienti_root', 
        //     ],
        //     // Files to avoid in backup accross all directories
        //     'skipFiles' => [
        //         '.gitignore',
        //     ]
        
        //     // 'class' => 'amoracr\backup\Backup',
        //     // 'backupDir' => 'backups',
        //     // 'databases' => ['db'],
        //     // // Directories that will be added to backup
        //     // 'directories' => [
        //     //     'images' => '@app/web/images',
        //     // ],
        // ],
        // 'backup' => [
        //     'class' => 'demi\backup\Component',
        //     // The directory for storing backups files
        //     'backupsFolder' => dirname(__DIR__) . '/backups', 
        //     // Directories that will be added to backup
        //     'directories' => [
        //         'images' => '@app/web/images',
        //         // 'uploads' => '@backend/uploads',
        //     ],
        // ],
    ],
    'modules' => $modules, 
    'params' => $params,
    /*
    'controllerMap' => [
        'fixture' => [ // Fixture generation command line.
            'class' => 'yii\faker\FixtureController',
        ],
    ],
    */
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
