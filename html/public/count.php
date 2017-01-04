<?php

// OUT側の処理

require("db.php");

$id = $_GET['id'];	// acc
$url= $_POST['url'];

$matchID = 0;
if(BID_MATCH){
	$ref = blogRefer($id);
	foreach($ref as $k=>$v){
		foreach($v as $kk=>$vv){
			if(preg_match("@$vv@",$url,$m)){
				$matchID = $kk;
			}
		}
	}
}

if($url!=''){
	if($log = apc_fetch("rsswdg_outlog_raw_".$id)){
		$log = unserialize($log);
	}
	$nowLog = array('url'=>$url,'acc'=>$id,'utime'=>time(),'ip'=>$_SERVER['REMOTE_ADDR'],'blogid'=>$matchID); 
	$log[] = $nowLog;
	apc_store('rsswdg_outlog_raw_'.$id, serialize($log), 3600*24);	// 24時間毎
}

// サイトOUT
$cntAry = array();
if($matchID!=0){
	if(!$_cnt=apc_fetch("rsswdg_outlog_".$id)){
		$cntAry[$matchID]=1;
	}else{
		$cntAry = unserialize($_cnt);
		$cntAry[$matchID]+=1;
	}
	apc_store('rsswdg_outlog_'.$id,serialize($cntAry),3600*24);
}

/*echo '<h3>outlog_raw</h3>';
var_dump(unserialize(apc_fetch("rsswdg_outlog_raw_".$id)));
echo '<h3>inlog_raw</h3>';
var_dump(unserialize(apc_fetch("rsswdg_inlog_raw_".$id)));

echo '<h3>inlog</h3>';
var_dump(unserialize(apc_fetch("rsswdg_inlog_".$id)));
echo '<h3>outlog</h3>';
var_dump(unserialize(apc_fetch("rsswdg_outlog_".$id)));
*/
var_dump($nowLog);