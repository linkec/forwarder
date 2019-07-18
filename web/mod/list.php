<?php
$count = $db->querySingle("SELECT COUNT(*) FROM rules");

$page = isset($_GET['page']) ? $_GET['page'] : 1;
$limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
$field = isset($_GET['field']) ? $_GET['field'] : 'id';
$order = isset($_GET['order']) ? $_GET['order'] : 'asc';
if(!in_array($field,array('id','protocol','local','localPort','remote','remotePort','switch','traffic_in','traffic_out','thread')) || !in_array($field,array('asc','desc'))){
    $field = 'id';
    $order = 'asc'; 
}
$data = array();
$code = 0;
$msg = '';
$start_num = ($page-1)*$limit;
$q = $db->query("SELECT * FROM rules ORDER BY $field $order LIMIT $start_num,$limit");
if($q){
    while($rs = $q->fetchArray(SQLITE3_ASSOC) ){
        $data[] = $rs;
    }
}else{
    $code = 500;
    $msg = 'DB Error';
}
$out['code'] = $code;
$out['msg'] = $msg;
$out['count'] = $count;
$out['data'] = $data;
echo json_encode($out);
return;