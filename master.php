<?php
define('APPROOT', __DIR__ );
define('PHPCLI', 'php' );
define('USERNAME','admin');
define('PASSWORD','asdasd');

use Workerman\Worker;
use \Workerman\WebServer;
require_once __DIR__.'/Workerman/Autoloader.php';

$webserver = new WebServer('http://0.0.0.0:18512');
$webserver->addRoot('forwarder', __DIR__ . '/web/');
$webserver->onWorkerStop = function($worker){
    $pids = glob(__DIR__.'/pids/*.pid');
    foreach($pids as $pidFile){
        $pid = file_get_contents($pidFile);
        posix_kill($pid,SIGINT);
    }
};
Worker::runAll();