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
$pidFile = APPROOT."/pids/{$rs['local']}_{$rs['localPort']}.pid";
if(file_exists($pidFile)){
    $pid = file_get_contents($pidFile);
    posix_kill($pid,SIGINT);
}
$db->exec("DELETE FROM rules WHERE id=$id");
$out['error'] = false;
$out['msg'] = 'Rule has been deleted.';
echo json_encode($out);
return;