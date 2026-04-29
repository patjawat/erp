<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

// Session ฝั่งเซิร์ฟเวอร์ให้สอดคล้องกับ cookie lifetime (เดียวกับ remember / session ใน params)
$sessionLifetime = $params['session.cookieLifetime'] ?? 3600;
if (function_exists('ini_set')) {
    ini_set('session.gc_maxlifetime', (string) $sessionLifetime);
}
$db2 = require __DIR__ . '/db2.php';
$modules = require __DIR__ . '/add_modules.php';
$version = require __DIR__ . '/version.php';

$cookieSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

$config = [
    'id' => 'basic',
    'version' => $version,
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'mobile'],
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
        'thaiFormatter' => [
            'class' => 'dixonsatit\thaiYearFormatter\ThaiYearFormatter',
        ],
        'telegram' => [
            'class' => 'app\components\Telegram',
            // ไม่ต้องใส่ botToken เพราะดึงจาก DB
        ],
        'thaidAuth' => [
            'class' => \app\modules\auth\components\ThaidAuth::class,
        ],
        'thaiDate' => [
            'class' => 'app\components\ThaiDate',
        ],
        'zip' => [
            'class' => 'app\components\ZipComponent',
        ],
        'cart' => [
            'class' => 'asyou99\cart\Cart',
        ],
        'cartMain' => [
            'class' => 'asyou99\cart\Cart',
            'storage' => [
                'class' => 'asyou99\cart\MultipleStorage',
                'storages' => [
                    ['class' => 'asyou99\cart\SessionStorage'],
                    [
                        'class' => 'asyou99\cart\DatabaseStorage',
                        'table' => 'cart_main',
                    ],
                ],
            ]
        ],
        'cartSub' => [
            'class' => 'asyou99\cart\Cart',
            'storage' => [
                'class' => 'asyou99\cart\MultipleStorage',
                'storages' => [
                    ['class' => 'asyou99\cart\SessionStorage'],
                    [
                        'class' => 'asyou99\cart\DatabaseStorage',
                        'table' => 'cart_sub',
                    ],
                ],
            ]
        ],
        'committee' => [
            'class' => 'asyou99\cart\Cart',
        ],
        'site' => 'app\components\SiteHelper',
        'employee' => 'app\components\UserHelper',
        // แจ่งเตือน line Group
        'LineMsg' => [
            'class' => 'app\components\LineMsg',
        ],
        'image' => [
            'class' => 'yii\image\ImageDriver',
            'driver' => 'GD',  // GD or Imagick
        ],
        'i18n' => [
            'translations' => [
                'app*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@app/messages',
                    'sourceLanguage' => 'en-US',
                    'fileMap' => [
                        'app' => 'app.php',
                        'app/error' => 'error.php',
                    ],
                ],
            ],
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
        'line' => [
            'class' => 'app\components\LineAuthClient',
            'channelId' => '2006812489',
            'channelSecret' => '0dd15c7dcb72e4623206c18d2d013eb6',
            'redirectUri' => 'https://c490-1-0-238-66.ngrok-free.app/line/default/line-login',
        ],
        'authClientCollection' => [
            'class' => 'yii\authclient\Collection',
            'clients' => [
                'google' => [
                    'class' => 'yii\authclient\clients\Google',
                    'clientId' => '261870609037-luvd13t9s0nabihs4tdg3d14knf93mk1.apps.googleusercontent.com',
                    'clientSecret' => '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',
                ],
                'line' => [
                    'class' => 'app\components\LineAuthClient',
                    'clientId' => '2006812489', // ใส่ Line Channel ID ของคุณ
                    'clientSecret' => '0dd15c7dcb72e4623206c18d2d013eb6', // ใส่ Line Channel Secret ของคุณ
                    'returnUrl' => 'https://c490-1-0-238-66.ngrok-free.app/line/default/auth?authclient=line', // URL สำหรับ Callback
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

        'view' => [
            'theme' => [
                'pathMap' => [
                    '@app/views' => '@app/themes/v4'
                ],
            ],
        ],
        'request' => [
            'cookieValidationKey' => 'UDNVjHYuFN4F2HiYRvQjPXW-kbcki6C8',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ]
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => 'redis',
            'port' => env('REDIS_PORT'),
            'database' => 0,
        ],

        // Session เก็บใน DB เพื่อให้ Dashboard "กำลังออนไลน์" และ /usermanager/session ทำงานได้
        // ต้องรัน migration สร้างตาราง session ก่อน: php yii migrate --migrationPath=@app/migrations
        'session' => [
            'class' => 'yii\web\DbSession',
            'sessionTable' => '{{%session}}',
            'cookieParams' => [
                'lifetime' => $params['session.cookieLifetime'] ?? 0,
                'httpOnly' => true,
                'sameSite' => 'Lax',
                'secure' => $cookieSecure,
            ],
            'writeCallback' => function ($session) {
                $userId = null;
                if (Yii::$app->has('user')) {
                    try {
                        $userId = Yii::$app->user->getIsGuest() ? null : Yii::$app->user->getId();
                    } catch (\Throwable $e) {
                        $userId = null;
                    }
                }
                return [
                    'user_id' => $userId,
                    'ip_address' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null,
                    'login_time' => time(),
                ];
            },
        ],
        'user' => [
            'identityClass' => 'app\modules\usermanager\models\User',
            'loginUrl' => ['/auth/login'],
            'enableAutoLogin' => true,
            'enableSession' => true,
            // Idle: ไม่มีกิจกรรมเกิน X วินาที → logout
            'authTimeout' => $params['user.idleTimeoutSeconds'] ?? 3600,
            // Absolute: นับจากเวลาล็อกอิน — สอดคล้อง remember 1 วัน
            'absoluteAuthTimeout' => $params['user.workingDaySeconds'] ?? (3600 * 24),
            'identityCookie' => [
                'name' => '_identity_erp',
                'httpOnly' => true,
                'sameSite' => 'Lax',
                'secure' => $cookieSecure,
            ],
        ],
        'authManager' => [
            'class' => 'dektrium\rbac\components\DbManager',
            // 'class' => 'yii\rbac\DbManager',
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
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
            'rules' => [
                // 'am/asset/bulk-create' => 'am/asset-bulk/bulk-create',
                // 'am/asset/transfer' => 'am/asset-lifecycle/transfer',
                // 'am/asset/repair' => 'am/asset-lifecycle/repair',
                // 'am/asset/dispose' => 'am/asset-lifecycle/dispose',
                // 'am/asset/print-qr' => 'am/asset-lifecycle/print-qr',
                // 'am/dashboard' => 'am/default/index',
                // 'am/report/register' => 'am/report/register',
                // 'am/report/depreciation-report' => 'am/report/depreciation-report',
                // 'am/report/movement-report' => 'am/report/movement-report',
                // 'am/report/survey-report' => 'am/report/survey-report',
                // 'am/report/monthly-depreciation' => 'am/report/monthly-depreciation',
                // 'am/depreciation/monthly-processing' => 'am/depreciation/monthly-processing',
                // 'api/asset/scan' => 'am/asset-api/scan',
                // กฎสำหรับ Webhook โดยเฉพาะ
                'POST webhook/receive' => 'dms/webhook/receive',
                // โปรไฟล์ของฉัน
                'me/profile' => 'me/default/profile',
                'me/account' => 'me/default/account',
            ],
        ],
    ],
    'modules' => $modules,
    'params' => $params,
    'as checkConfig' => [
        'class' => 'app\components\CheckMaintenanceMode',
    ],
    'as access' => [
        'class' => 'mdm\admin\components\AccessControl',
        'allowActions' => [
            'telegrambot/*',
            'dms/webhook/receive',
            // '*',
            'line/*',
            // 'me/*',
            // 'line-group/*',
            'summary/*',
            'approve/*',
            'finance/*',
            'debug/*',
            'hr/leave/cal-days',
            'hr/leave/get-leader-approve',
            'hr/document/*',
            'dms/documents/comment-validator',
            'hr/leave/create-validator',
            'purchase/order/add-item/*',
            'purchase/order/product-list/*',
            'purchase/pr-order/checkervalidator',
            'purchase/order-item/validator',
            'purchase/pr-order/createvalidator',
            '/purchase/pq-order/validator',
            'inventory/stock-in/add-item',
            'inventory/main-stock/add-to-cart',
            'inventory/main-stock/show-cart',
            'inventory/main-stock/view-cart',
            'inventory/stock-in/create-validator',
            'inventory/stock-order/update-lot-validator/*',
            'inventory-v2/main-stock/dashboard',
            'inventory-v2/main-stock/items-with-stock',
            'inventory-v2/main-stock/items-with-stock-offcanvas',
            'inventory-v2/main-stock/critical-items-offcanvas',
            'inventory-v2/main-stock/export-items-with-stock-excel',
            'inventory-v2/main-stock/export-critical-items-excel',
            'helpdesk/repair/create-validator',
            'helpdesk/repair/technician-list',
            'filemanager/*',
            // 'usermanager/*',
            'site/login',
            'site/logout',
            'site/sign-up',
            'site/success',
            'site/conditions-register',
            'site/forgot-password',
            'site/reset-password',
            'site/accept-condition',
            'datecontrol/parse/convert',
            'depdrop/*',
            'auth/*',
            'mobile/auth/*',
            'mobile/error/*',
            'health/health-screen/validator',
            'gii/*',
            // 'hr/*',
            // 'sm/*',
            // 'am/*',
            // 'pm/*',
            'fsn/*',
            // 'lm/*',
            // 'inventory/*',
            // 'stock/*',
            'backoffice/*',
            'setting/*',
            'treemanager/*',
            'ms-word/*',
            // 'datecontrol/parse/convert',
            // 'reception/default/index',
            // 'reception/default/form-upload',
            // 'document/documentqr/upload-ajax',
            // 'gii/*',
            'api/*',
            'pdf-template/template/print-sample',
        ],
    ],
    'controllerMap' => [
        // 'auth' => 'app\modules\usermanager\controllers\AuthController',
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
        // 'allowedIPs' => ['*'],
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
        // 'allowedIPs' => ['127.0.0.1', '::1'],
        // 'allowedIPs' => ['*'],
    ];
}

return $config;
