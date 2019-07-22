<?php
$id   = isset($_GET['id'])      ? $_GET['id']     : null;
if(!$id){
    $out['error'] = true;
    $out['errmsg'] = 'ID can not be null.';
    echo json_encode($out);
    return;
}
$rs = $db->querySingle("SELECT COUNT(*) FROM rules WHERE id=$id");
if(!$rs){
    $out['error'] = true;
    $out['errmsg'] = 'Rule not found.';
    echo json_encode($out);
    return;
}
$rs = $db->querySingle("SELECT * FROM rules WHERE id=$id",true);

$protocol   = $rs['protocol'];
$local      = $rs['local'];
$localPort  = $rs['localPort'];
$remote     = $rs['remote'];
$remotePort = $rs['remotePort'];
$thread     = $rs['thread'];
$switch     = $rs['switch'];

//Check is Port inuse?
if (!checkPort($protocol,$local,$localPort)) {
    $error = true;
    $out['error'] = $error;
    $out['errmsg'][] = "Can not bind on $protocol://$local:$localPort";
    echo json_encode($out);
    return;
}

$cmd = shell_exec(PHPCLI . " " . APPROOT ."/slaver.php start --local=$local --local-port=$localPort --remote=$remote --remote-port=$remotePort --thread=$thread --protocol=$protocol");
$out['return'] = $cmd;
$db->exec("UPDATE rules SET switch=1 WHERE id=$id");
$out['error'] = false;
$out['msg'] = 'Rule has been enabled.';
echo json_encode($out);
return;