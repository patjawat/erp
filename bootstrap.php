// bootstrap.php
<?php
/**
 * @author xialeistudio
 * @date 2019-05-17
 */

use swoole\foundation\web\Server;
use Swoole\Runtime;

// Warning: singleton in coroutine environment is untested!
Runtime::enableCoroutine();
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', getenv('PHP_ENV') === 'development' ? 'dev' : 'prod');

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

// require your server configuration
$config = require __DIR__ . '/config/server.php';
// construct a server instance
$server = new Server($config);
// start the swoole server
$server->start();