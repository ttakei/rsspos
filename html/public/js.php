<?php

// ここはもうPHP処理でよい
// IN側の処理だけ入れておく

require("db.php");

$id = $_GET['id'];
$Gref = $_GET['r'];

// 2015-03-04 全てのlogを保存
// http://blog.livedoor.jp/kaikaihanno/
// http://streakingfc2.blog.fc2.com/
//if($Gref=='' || $Gref=='x' || $Gref=='h') exit;
if(!preg_match('@\d+?\.\d+?\.\d+?\.\d+?$@',$_SERVER['REMOTE_ADDR'])) exit;

if(preg_match('@http://blog.livedoor.jp/@',$Gref,$m)){
	preg_match('@http://blog.livedoor.jp/.+?/@',$Gref,$mm);
	$url = $mm[0];
}else{
	preg_match('@http://(.*?)/@',$Gref,$mm);
	$url = $mm[0];
}
/*
preg_match('@http://(blog.livedoor.jp)/(.*?)$@',$Gref,$m);
preg_match('@(.*?)/@',$m[2],$mm);

if(!count($mm)){
	$url = "http://".$m[1];
}else{
	$url = "http://".$m[1]."/".$mm[1];
}
*/
if(!$log = apc_fetch('rsswdg_inlog_all_'.$id)){
	$log = array('utime'=>time(),'ip'=>$_SERVER['REMOTE_ADDR'],'acc'=>$id,'refer'=>$Gref,'url'=>$url);
}else{
	$log = unserialize($log);
	$log[] = array('utime'=>time(),'ip'=>$_SERVER['REMOTE_ADDR'],'acc'=>$id,'refer'=>$Gref,'url'=>$url);
}
apc_store("rsswdg_inlog_all_".$id,serialize($log),3600*24);

// 不正対策
// IPを保存しておき、同じreferからのアクセスは弾く

if(IP_FILTER){
	$ip = ipload($id);
}else{
	$ip = array();
}

$matchID = 0;
if(BID_MATCH){
	$ref = blogRefer($id);
	foreach($ref as $k=>$v){
		foreach($v as $kk=>$vv){
			if(preg_match("@$vv@",$Gref,$m)){
				$matchID = $kk;
			}
		}
	}
}

/*echo "<h3>acc</h3>";
var_dump($id);
echo "<h3>matchID</h3>";
var_dump($matchID);
echo "<h3>ip</h3>";
var_dump($ip);
echo "<h3>ref</h3>";
var_dump($ref);*/

$cntAry = array();
if($matchID!=0){
	if(!$_cnt=apc_fetch("rsswdg_inlog_".$id)){
		$cntAry[$matchID]=1;
	}else{
		$cntAry = unserialize($_cnt);
		$cntAry[$matchID]+=1;
	}
	apc_store('rsswdg_inlog_'.$id,serialize($cntAry),3600*24);
}


if(!in_array($_SERVER['REMOTE_ADDR'],$ip) && $matchID!=0){
	if(APC){
		if(!$log = apc_fetch('rsswdg_inlog_raw_'.$id)){
			$log = array('utime'=>time(),'ip'=>$_SERVER['REMOTE_ADDR'],'acc'=>$id,'refer'=>$Gref,'blogid'=>$matchID);
		}else{
			$log = unserialize($log);
			$log[] = array('utime'=>time(),'ip'=>$_SERVER['REMOTE_ADDR'],'acc'=>$id,'refer'=>$Gref,'blogid'=>$matchID);
		}
		apc_store("rsswdg_inlog_raw_".$id,serialize($log),3600*24);
	}else{
		//$con = mysql_pconnect('localhost','root','jahguleth') or die ("DBERR-NC");
		$s = sprintf("INSERT rsswidget.inlog SET refer='%s',ip='%s',utime=%d",$Gref,$_SERVER['REMOTE_ADDR'],date("U"));
		mysql_query($s) or die(mysql_error().$s);
	}
	upip($id);
}

/*echo "<h3>now log</h3>";
var_log($log);*/

//echo "<h3>apc_log</h3>";
//var_dump(unserialize(apc_fetch('rsswdg_inlog_'.$id)));