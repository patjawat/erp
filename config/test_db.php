<?php
$db = require __DIR__ . '/db.php';
// test database! Important not to run tests on production or development databases
$db['dsn'] = getenv('TEST_DB_DSN') ?: 'mysql:host=localhost;dbname=yii2basic_test';

return $db;
