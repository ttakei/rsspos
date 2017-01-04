<?php

// APC(Queue)に入れ込む
define("APC",1);
define("IP_FILTER",1);
define("BID_MATCH",1);

$con = mysql_pconnect('localhost','rsswidget','ASvzTAHAyJrM3Xa') or die ("DBERR-NC");
mysql_select_db('rsswidget',$con) or die("DBERR-NonDB");

function sqlesc($str){
	return mysql_real_escape_string($str);
}

// カウント済みIPアドレスを保存
function upip($id){
	if(!$d=apc_fetch("rsswdg_in_ip_".$id)){
		$d = array();
	}else{
		$d = unserialize($d);
	}
	$d[]=$_SERVER['REMOTE_ADDR'];

	if($_SERVER['REMOTE_ADDR']!='180.19.234.71'){
		apc_store("rsswdg_in_ip_".$id,serialize($d),3600*24);
	}
}

// IPデータ取得
function ipload($id){
	return unserialize(apc_fetch("rsswdg_in_ip_".$id));
}

// リファ置換設定をキャッシュしておく
function blogRefer($id){
	if(!$d=apc_fetch("rsswdg_refer_".$id)){
		$s = sprintf("SELECT id,acc,refer FROM rsswidget.blogs WHERE acc='%s' AND deleted_at='0000-00-00 00:00:00'",$id);
		$q = mysql_query($s) or die(mysql_error());
		while($r=mysql_fetch_assoc($q)){
			$t = explode(",",$r['refer']);	// refer複数対応
			$key = $r['id'];
			foreach($t as $v){
				$d[][$key] = $v;
			}
		}
	}else{
		$d = unserialize($d);
	}
	return $d;
}
