<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';
$db2 = require __DIR__ . '/db2.php';
$modules = require __DIR__ . '/add_modules.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'homeUrl' => ['/site'],
    'timeZone' => 'Asia/Bangkok',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'container' => [
        'definitions' => [
            'yii\widgets\LinkPager' => [
                'maxButtonCount' => 6,
                'options' => [
                    'tag' => 'ul',
                    'class' => 'pagination',  // pagination-sm
                ]
            ],
        ],
    ],
    'language' => 'th_TH',  // เปิดใช้งานภาษาไทย
    'components' => [
        'zip' => [
            'class' => 'app\components\ZipComponent',
        ],
        'cart' => [
			'class' => 'asyou99\cart\Cart',
		],
        'cart2' => [
			'class' => 'asyou99\cart\Cart',
		],
        'committee' => [
			'class' => 'asyou99\cart\Cart',
		],
        
        'employee' => 'app\components\UserHelper',
        // แจ่งเตือน line Group
        'lineNotify' => [
            'class' => 'app\components\LineNotify',
        ],
        'image' => [
            'class' => 'yii\image\ImageDriver',
            'driver' => 'GD',  // GD or Imagick
        ],
        'formatter' => [
            'class' => 'yii\i18n\Formatter',
            'dateFormat' => 'php:d/m/Y',
            'datetimeFormat' => 'php:d/m/Y H:i:s',
            'timeFormat' => 'php:H:i:s',
            'defaultTimeZone' => 'Asia/Bangkok',
            'nullDisplay' => '',
            'locale' => 'th_TH',
        ],
        // 'formatter' => [
        //     'class' => 'yii\i18n\Formatter',
        //     'nullDisplay' => '-',
        // ],
        'authClientCollection' => [
            'class' => 'yii\authclient\Collection',
            'clients' => [
                'google' => [
                    'class' => 'yii\authclient\clients\Google',
                    'clientId' => '261870609037-luvd13t9s0nabihs4tdg3d14knf93mk1.apps.googleusercontent.com',
                    'clientSecret' => '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',
                ],
            ],
        ],
        'assetManager' => [
            'bundles' => [
                'kartik\form\ActiveFormAsset' => [
                    'bsDependencyEnabled' => false  // do not load bootstrap assets for a specific asset bundle
                ],
            ],
        ],
        'thaiFormatter' => [
            'class' => 'dixonsatit\thaiYearFormatter\ThaiYearFormatter',
        ],
        'view' => [
            'theme' => [
                'pathMap' => [
                    '@app/views' => '@app/themes/v3'
                ],
            ],
        ],
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'UDNVjHYuFN4F2HiYRvQjPXW-kbcki6C8',
            // 'class' => 'app\components\Request',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        // 'user' => [
        //     'identityClass' => 'app\models\User',
        //     'enableAutoLogin' => true,
        // ],
        'user' => [
            // 'identityClass' => 'mdm\admin\models\User',
            'identityClass' => 'app\modules\usermanager\models\User',
            'loginUrl' => ['/site/login'],
            'enableAutoLogin' => false,
            'enableSession' => true,
            // ตั้งเวลา timeout 1 ชั่วโมง 60 วินาที * 60 นาที
            // 'authTimeout' => 12960000,
        ],
        'authManager' => [
            'class' => 'dektrium\rbac\components\DbManager',
            // 'class' => 'yii\rbac\DbManager',
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        // 'mailer' => [
        //     'class' => 'yii\swiftmailer\Mailer',
        //     // send all mails to a file by default. You have to set
        //     // 'useFileTransport' to false and configure transport
        //     // for the mailer to send real emails.
        //     'useFileTransport' => true,
        // ],
        // 'mailer' => [ //กำหนดการส่ง Email ผ่าน SMTP ของ Google
        //     'class' => 'yii\swiftmailer\Mailer',
        //     'viewPath' => '@app/mail',
        //     // send all mails to a file by default. You have to set
        //     // 'useFileTransport' to false and configure a transport
        //     // for the mailer to send real emails.
        //     'useFileTransport' => false,
        //     'transport' => [
        //         'class' => 'Swift_SmtpTransport',
        //         'host' => 'smtp.google.com',
        //         'username' => 'patjawat@gmail.com', //user ทีจะใช้ smtp
        //         'password' => 'Patjawat@2528x',//รหัสผ่านของ user
        //         'port' => '587',
        //         'encryption' => 'ssl',
        //     ],
        // ],
        'mailer' => [  // กำหนดการส่ง Email ผ่าน SMTP ของ Google
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@app/mail',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => false,
            // 'transport' => [
            //     'class' => 'Swift_SmtpTransport',
            //     'host' => 'outgoing.mail.go.th',
            //     'username' => 'adirak.muan@moph.go.th', //user ทีจะใช้ smtp
            //     'password' => 'Moph1234',//รหัสผ่านของ user
            //     'port' => '465',
            //     'encryption' => 'ssl',
            // ],
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'smtp.gmail.com',
                'username' => 'patjawat@gmail.com',  // user ทีจะใช้ smtp
                'password' => 'Patjawat@2528',  // รหัสผ่านของ user
                'port' => '465',
                'encryption' => 'ssl',
                // 'encryption' => 'tls',
            ],
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'db2' => $db2,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [],
        ],
    ],
    'modules' => $modules,
    'params' => $params,
    'as access' => [
        'class' => 'mdm\admin\components\AccessControl',
        'allowActions' => [
            // '*',
            'line/*',
            // 'me/*',
            'line-group/*',
            'summary/*',
            'helpdesk/*',
            // 'purchase/*',
            'debug/*',
            'purchase/pr-order/checkervalidator',
            'purchase/order-item/validator',
            'purchase/pr-order/createvalidator',
            'filemanager/*',
            // 'usermanager/*',
            'site/login',
            'site/logout',
            'site/sign-up',
            'site/success',
            'site/conditions-register',
            'site/forgot-password',
            'site/reset-password',
            'datecontrol/parse/convert',
            'depdrop/*',
            'auth/*',
            'gii/*',
            'hr/*',
            'sm/*',
            'am/*',
            'pm/*',
            'fsn/*',
            'inventory/*',
            'stock/*',
            'backoffice/*',
            'setting/*',
            'treemanager/*',
            'ms-word/*',
            // 'datecontrol/parse/convert',
            // 'reception/default/index',
            // 'reception/default/form-upload',
            // 'document/documentqr/upload-ajax',
            // 'gii/*',
            'api/*'
        ],
    ],
    'controllerMap' => [
        'auth' => 'app\modules\usermanager\controllers\AuthController',
        'elfinder' => [
            'class' => 'mihaildev\elfinder\Controller',
            'access' => ['@'],  // глобальный доступ к фаил менеджеру @ - для авторизорованных , ? - для гостей , чтоб открыть всем ['@', '?']
            'disabledCommands' => ['netmount'],  // отключение ненужных команд https://github.com/Studio-42/elFinder/wiki/Client-configuration-options#commands
            'roots' => [
                [
                    'baseUrl' => '@web',
                    'basePath' => '@webroot',
                    'path' => 'files/global',
                    'name' => 'Global'
                ],
                [
                    'class' => 'mihaildev\elfinder\volume\UserPath',
                    'path' => 'files/user_{id}',
                    'name' => 'My Documents'
                ],
            ],
            'watermark' => [
                'source' => __DIR__ . '/logo.png',  // Path to Water mark image
                'marginRight' => 5,  // Margin right pixel
                'marginBottom' => 5,  // Margin bottom pixel
                'quality' => 95,  // JPEG image save quality
                'transparency' => 70,  // Water mark image transparency ( other than PNG )
                'targetType' => IMG_GIF | IMG_JPG | IMG_PNG | IMG_WBMP,  // Target image formats ( bit-field )
                'targetMinPixel' => 200  // Target image minimum pixel size
            ]
        ]
    ],
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';

    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        // 'allowedIPs' => ['127.0.0.1', '::1'],
        'allowedIPs' => ['*'],
    ];
    $config['modules']['gii']['class'] = 'yii\gii\Module';
    $config['modules']['gii']['generators'] = [
        'kartikgii-crud' => ['class' => 'warrence\kartikgii\crud\Generator'],
    ];

    $config['modules']['hitCounter'] = ['class' => 'coderius\hitCounter\Module'];
    $config['bootstrap'][] = 'coderius\hitCounter\config\Bootstrap';
    $config['bootstrap'][] = 'gii';

    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['127.0.0.1', '::1'],
        'allowedIPs' => ['*'],
    ];
}

return $config;
