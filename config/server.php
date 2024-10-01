// config/server.php
<?php
  return [
   'host' => 'localhost',
   'port' => 9501,
   'mode' => SWOOLE_PROCESS,
   'sockType' => SWOOLE_SOCK_TCP,
   'app' => require __DIR__ . '/swoole.php', 
   'options' => [ // options for swoole server
       'pid_file' => __DIR__ . '/../runtime/swoole.pid',
       'worker_num' => 2,
       'daemonize' => 0,
       'task_worker_num' => 2,
   ]
];