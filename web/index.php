<?php
if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
    Workerman\Protocols\Http::header('WWW-Authenticate: Basic realm="Forwarder"');
    Workerman\Protocols\Http::header('HTTP/1.0 401 Unauthorized');
    echo 'Unauthorized';
    return;
}else{
    list($username,$password) = explode(":",base64_decode(str_ireplace("Basic ",'',$_SERVER['HTTP_AUTHORIZATION'])));
    if($username!=USERNAME || $password!=PASSWORD){
        Workerman\Protocols\Http::header('WWW-Authenticate: Basic realm="Forwarder"');
        Workerman\Protocols\Http::header('HTTP/1.0 401 Unauthorized');
        echo 'Unauthorized';
        return;
    }
}

$db = new SQLite3(APPROOT.'/data.db');
if(!$db){
    echo 'faild to open data.db';
    return;
}
$ac = isset($_GET['ac']) ? $_GET['ac'] : 'index';
$modFile = __DIR__.'/mod/'.$ac.'.php';
// var_dump($modFile);
if(file_exists($modFile)){
    include $modFile;
}else{
    echo 'Error';
}
?>