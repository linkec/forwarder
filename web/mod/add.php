<?php
$protocol   = isset($_POST['protocol'])      ? $_POST['protocol']     : null;
$local      = isset($_POST['local'])         ? $_POST['local']        : null;
$localPort  = isset($_POST['local-port'])    ? $_POST['local-port']   : null;
$remote     = isset($_POST['remote'])        ? $_POST['remote']       : null;
$remotePort = isset($_POST['remote-port'])   ? $_POST['remote-port']  : null;
$thread     = isset($_POST['thread'])        ? $_POST['thread']       : 1;
$switch     = isset($_POST['switch'])        ? $_POST['switch']       : true;

//Validation
$error = false;
if(!in_array($protocol,array('tcp','udp'))){
    $error = true;
    $errmsg[] = 'Protocol should be tcp or udp.';
}
if(!filter_var($local,FILTER_VALIDATE_IP)){
    $error = true;
    $errmsg[] = 'Local Address should be an validate IP.';
}
if(!filter_var($remote,FILTER_VALIDATE_IP)){
    $error = true;
    $errmsg[] = 'Remote Address should be an validate IP.';
}
if($localPort>65535 || $localPort<1){
    $error = true;
    $errmsg[] = 'Local Port should between 1-65535.';
}
if($remotePort>65535 || $remotePort<1){
    $error = true;
    $errmsg[] = 'Remote Port should between 1-65535.';
}
if($thread>64 || $thread<1){
    $thread = 1;
}
$switch = $switch ? true : false;
if($error){
    $out['error'] = $error;
    $out['errmsg'] = $errmsg;
    echo json_encode($out);
    return;
}

//Check is Port inuse?
$socket = stream_socket_server("$protocol://$local:$localPort", $errno, $errstr,$protocol === 'udp' ? STREAM_SERVER_BIND : STREAM_SERVER_LISTEN);
if (!$socket) {
    $error = true;
    $out['error'] = $error;
    $out['errmsg'][] = "Can not bind on $protocol://$local:$localPort";
    echo json_encode($out);
    return;
}
fclose($socket);
unset($socket);
//TABLE COLUMNS
//id,protocol,local,localPort,remote,remotePort,switch,traffic_in,traffic_out,thread
$db->exec("INSERT INTO rules VALUES (NULL, '$protocol', '$local', $localPort, '$remote', $remotePort, $switch, 0, 0, $thread);");
if($switch){
    $cmd = shell_exec(PHPCLI . " " . APPROOT ."/slaver.php start --local=$local --local-port=$localPort --remote=$remote --remote-port=$remotePort --thread=$thread --protocol=$protocol");
    $out['return'] = $cmd;
}
$out['error'] = false;
$out['msg'] = 'Forward Rule Add Succeed.';
echo json_encode($out);
return;
