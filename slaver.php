<?php
use Workerman\Worker;
use Workerman\Connection\AsyncTcpConnection;
require_once __DIR__.'/Workerman/Autoloader.php';

$config = array(
    'protocol'=>'tcp',
    'remote'=> "127.0.0.1",
    'remote-port'=>80,
    'local'=>'0.0.0.0',
    'local-port'=>80,
    'thread'=>1,
);
foreach($argv as $v){
    $_tmp = explode('=',$v);
    if(count($_tmp)>1){
        $config[substr($_tmp[0],2)] = $_tmp[1];
    }
}
$error = false;
foreach($config as $k=>$v){
    switch ($k) {
        case 'protocol':
            if(!in_array($v,array('tcp','udp'))){
                $error = true;
                $errmsg[] = 'CONFIG:protocol should be tcp/udp.';
            }
            break;
        case 'remote':
        case 'local':
            if(!filter_var($v,FILTER_VALIDATE_IP)){
                $error = true;
                $errmsg[] = "CONFIG:$k should be an IP.";
            }
            break;
        case 'remote-port':
        case 'local-port':
            if($v>65535 || $v<1){
                $error = true;
                $errmsg[] = "CONFIG:$k should between 1-65535.";
            }
            break;
    }
}

if($error){
    echo implode("\n",$errmsg)."\n";
    exit();
}

!is_dir(__DIR__ .'/pids/')  && mkdir(__DIR__ .'/pids/');
!is_dir(__DIR__ .'/logs/')  && mkdir(__DIR__ .'/logs/');

Worker::$daemonize  = true;
Worker::$pidFile    = __DIR__ .'/pids/'.$config['local'].'_'.$config['local-port'].'.pid';
Worker::$logFile    = __DIR__ .'/logs/'.$config['local'].'_'.$config['local-port'].'.log';
Worker::$stdoutFile = __DIR__ .'/logs/'.$config['local'].'_'.$config['local-port'].'.log';

$worker = new Worker("{$config['protocol']}://{$config['local']}:{$config['local-port']}");
$worker->count = $config['thread'];
$worker->name = "Forwarder {$config['remote']}:{$config['remote-port']}";
$worker->onConnect = function($connection)use($config)
{
    $sub_connection = new AsyncTcpConnection("{$config['protocol']}://{$config['remote']}:{$config['remote-port']}");
    $connection->pipe($sub_connection);
    $sub_connection->pipe($connection);
    $sub_connection->connect();
};
Worker::runAll();