<?php
use Workerman\Worker;
use Workerman\Lib\Timer;
use Workerman\Connection\AsyncTcpConnection;
use Workerman\Connection\AsyncUdpConnection;
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
Worker::$pidFile    = __DIR__ .'/pids/'.$config['protocol'].'_'.$config['local'].'_'.$config['local-port'].'.pid';
Worker::$logFile    = __DIR__ .'/logs/'.$config['protocol'].'_'.$config['local'].'_'.$config['local-port'].'.log';
Worker::$stdoutFile = __DIR__ .'/logs/'.$config['protocol'].'_'.$config['local'].'_'.$config['local-port'].'.log';

$worker = new Worker("{$config['protocol']}://{$config['local']}:{$config['local-port']}");
$worker->count = $config['thread'];
$worker->bytesRead = 0;
$worker->bytesWritten = 0;
$worker->name = "Forwarder {$config['remote']}:{$config['remote-port']}";
$worker->onWorkerStart = function($worker)use($config)
{
    Timer::add(0.1,function()use($worker)
    {
        foreach($worker->connections as $connection){
            $worker->bytesRead += $connection->bytesRead-$connection->lastRead;
            $worker->bytesWritten += $connection->bytesWritten-$connection->lastWritten;
            $connection->lastRead = $connection->bytesRead;
            $connection->lastWritten = $connection->bytesWritten;
        }
    });
    Timer::add(1,function()use($worker,$config)
    {
        $db = new SQLite3(__DIR__.'/data.db');
        if(!$db){
            echo 'faild to open data.db';
            return;
        }
        $db->exec("UPDATE rules SET traffic_in={$worker->bytesRead},traffic_out={$worker->bytesWritten} WHERE protocol LIKE '{$config['protocol']}' AND `local` LIKE '{$config['local']}' AND localPort={$config['local-port']}");
        $db->close();
    });
};
if($config['protocol']=='tcp'){
    $worker->onConnect = function($connection)use($config)
    {
        $connection->lastRead = 0;
        $connection->lastWritten = 0;
        $sub_connection = new AsyncTcpConnection("{$config['protocol']}://{$config['remote']}:{$config['remote-port']}");
        $connection->pipe($sub_connection);
        $sub_connection->pipe($connection);
        $sub_connection->onClose = function($sub_connection)use($connection)
        {
            $connection->close();
        };
        $sub_connection->connect();
    };
}else{
    $worker->onMessage= function($connection,$data)use($config,$worker)
    {
        $sub_connection = new AsyncUdpConnection("{$config['protocol']}://{$config['remote']}:{$config['remote-port']}");
        $sub_connection->onConnect = function($sub_connection)use($data,$connection,$worker)
        {
            $worker->bytesRead += strlen($data);
            $sub_connection->send($data);
        };
        $sub_connection->onMessage = function($sub_connection,$sub_data)use($connection,$worker)
        {
            $worker->bytesWritten += strlen($sub_data);
            $connection->send($sub_data);
            $connection->close();
            $sub_connection->close();
        };
        $sub_connection->connect();
    };
}
Worker::runAll();