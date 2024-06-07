<?php
// define('DOTENV_FILE', '.env');
return [
    'class' => 'yii\db\Connection',
    'dsn' => env('DB2_DSN'),
    'username' => env('DB2_USERNAME'),
    'password' => env('DB2_PASS'),
    'charset' => 'utf8',
    // Schema cache options (for production environment)
    'enableSchemaCache' => true,
    'schemaCacheDuration' => 60,
    'schemaCache' => 'cache',
];
