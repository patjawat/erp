<?php
// define('DOTENV_FILE', '.env');
return [
    'class' => 'yii\db\Connection',
    'dsn' =>env('DB_DSN'),
    'username' => env('DB_USERNAME'),
    'password' => env('DB_PASS'),
    'charset' => 'utf8',

    // Schema cache options (for production environment)
    'enableSchemaCache' => true,
    'schemaCacheDuration' => 60,
    'schemaCache' => 'cache',
];
