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
$webserver->onWorkerStart = function($worker){
    $db = new SQLite3(APPROOT.'/data.db');
    if(!$db){
        echo 'faild to open data.db';
        return;
    }
    $q = $db->query("SELECT * FROM rules");
    if($q){
        while($rs = $q->fetchArray(SQLITE3_ASSOC)){
            $protocol   = $rs['protocol'];
            $local      = $rs['local'];
            $localPort  = $rs['localPort'];
            $remote     = $rs['remote'];
            $remotePort = $rs['remotePort'];
            $thread     = $rs['thread'];
            $switch     = $rs['switch'];
            if ($switch && checkPort($protocol,$local,$localPort)) {
                shell_exec(PHPCLI . " " . APPROOT ."/slaver.php start --local=$local --local-port=$localPort --remote=$remote --remote-port=$remotePort --thread=$thread --protocol=$protocol");
            }
        }
    }
};
$webserver->onWorkerStop = function($worker){
    $pids = glob(__DIR__.'/pids/*.pid');
    foreach($pids as $pidFile){
        $pid = file_get_contents($pidFile);
        posix_kill($pid,SIGINT);
        unlink($pidFile);
    }
};
Worker::runAll();

function get_size($s,$u='B',$p=1){
	$us = array('B'=>'K','K'=>'M','M'=>'G','G'=>'T');
	return (($u!=='B')&&(!isset($us[$u]))||($s<1024)) ? (number_format($s,$p)." $u") : (get_size($s/1024,$us[$u],$p));
}
function checkPort($protocol,$local,$localPort){
    $socket = stream_socket_server("$protocol://$local:$localPort", $errno, $errstr,$protocol === 'udp' ? STREAM_SERVER_BIND : STREAM_SERVER_LISTEN);
    if(!$socket){
        return false;
    }
    fclose($socket);
    unset($socket);
    return true;
}