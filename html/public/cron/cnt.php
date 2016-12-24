<?php

// カウント集計プログラム
// PV集計のための全ログ(inalllog)を86400秒過ぎたものは削除する
// INおよびOUTは現時点では削除していない。適宜削除の処理が必要かも。

// カウント集計期間

$TODAY_CNT = 1;
$period = 3600*24;

// 全てのアカウント情報を取得

require("../db.php");
//$con = mysql_pconnect('localhost','root','jahguleth') or die ("DBERR-NC");
$s = sprintf("SELECT acc FROM rsswidget.sites WHERE isactive=1 AND deleted_at='0000-00-00 00:00:00'");
$q = mysql_query($s);
while($r=mysql_fetch_assoc($q)){
	$acc[] = $r['acc'];
}

// ２週間過ぎたログは消す
$s = sprintf("DELETE FROM inlog WHERE utime<'%s'",date('U',strtotime("-2 weeks")));
mysql_query($s);
$s = sprintf("DELETE FROM outlog WHERE utime<'%s'",date('U',strtotime("-2 weeks")));
mysql_query($s);

//var_dump($acc);

foreach($acc as $k=>$v){

	// todo INカウント全ログ処理
	if($_cnt=apc_fetch("rsswdg_inlog_all_".$v)){
		$cntAry = unserialize($_cnt);

		$_tmp = '';
		foreach($cntAry as $kk=>$vv){
			if($vv['utime']>1000){
				$vv['acc'] = mysql_real_escape_string($vv['acc']);
				$vv['url'] = mysql_real_escape_string($vv['url']);
				$vv['refer'] = mysql_real_escape_string($vv['refer']);
				$vv['ip'] = mysql_real_escape_string($vv['ip']);
				$vv['utime'] = mysql_real_escape_string($vv['utime']);
				$_tmp .= "(".sprintf("'%s','%s','%s','%s',%d",$vv['acc'],$vv['url'],$vv['refer'],$vv['ip'],$vv['utime'])."),";
			}
		}

		$_tmp = substr($_tmp,0,-1);

		if(strlen($_tmp)>10){
			$s = sprintf("INSERT INTO rsswidget.inalllog VALUES %s",$_tmp);
			echo $s.'<br>';
			mysql_query($s) or die(mysql_error());
		}
	}

	// 1日間過ぎたログは消す

	$s = sprintf("DELETE FROM rsswidget.inalllog WHERE utime<%d",time()-3600*24*1);
	$q = mysql_query($s);

	echo '<hr>';

	// todo INカウント詳細ログ処理
	if($_cnt=apc_fetch("rsswdg_inlog_raw_".$v)){
		$cntAry = unserialize($_cnt);

		$_tmp = '';
		foreach($cntAry as $kk=>$vv){
			$_tmp .= "(".sprintf("'%s','%s','%s','%s',%d",$vv['acc'],$vv['blogid'],$vv['refer'],$vv['ip'],$vv['utime'])."),";
		}

		$_tmp = substr($_tmp,0,-1);

		$s = sprintf("INSERT INTO rsswidget.inlog VALUES %s",$_tmp);
		echo $s.'<br>';
		mysql_query($s) or die(mysql_error());
	}

	echo '<hr>';
	// todo outカウント詳細ログ処理
	if($_cnt=apc_fetch("rsswdg_outlog_raw_".$v)){
		$cntAry = unserialize($_cnt);

		$_tmp = '';
		foreach($cntAry as $kk=>$vv){
			$_tmp .= "(".sprintf("'%s','%s','%s','%s',%d",$vv['acc'],$vv['blogid'],$vv['url'],$vv['ip'],$vv['utime'])."),";
		}

		$_tmp = substr($_tmp,0,-1);

		$s = sprintf("INSERT INTO rsswidget.outlog VALUES %s",$_tmp);
		echo $s.'<br>';
		mysql_query($s) or die(mysql_error());

	}

	echo '<hr>';

	$s = sprintf("SELECT id,acc,name FROM rsswidget.blogs WHERE acc='%s'",$v);
	$q = mysql_query($s);
	while($r=mysql_fetch_assoc($q)){
		// IN
		if($TODAY_CNT){
			$s1 = sprintf("SELECT count(*) as cnt FROM rsswidget.inlog WHERE blogid=%d AND utime>%d",$r['id'],mktime(0,0,0,date("m"),date("d"),date("Y")));
		}else{
			$s1 = sprintf("SELECT count(*) as cnt FROM rsswidget.inlog WHERE blogid=%d AND utime>%d",$r['id'],time()-$period);
		}
		$q1 = mysql_query($s1) or die(mysql_error());
		$r1 = mysql_fetch_assoc($q1);

		$s1 = sprintf("UPDATE rsswidget.blogs SET `in`=%d WHERE id=%d",$r1['cnt'],$r['id']);
		mysql_query($s1) or die(mysql_error()."<br>$s1");

		// OUT
		if($TODAY_CNT){
			$s1 = sprintf("SELECT count(*) as cnt FROM rsswidget.outlog WHERE blogid=%d AND utime>%d",$r['id'],mktime(0,0,0,date("m"),date("d"),date("Y")));
		}else{
			$s1 = sprintf("SELECT count(*) as cnt FROM rsswidget.outlog WHERE blogid=%d AND utime>%d",$r['id'],time()-$period);
		}
		$q1 = mysql_query($s1) or die(mysql_error());
		$r1 = mysql_fetch_assoc($q1);

		$s1 = sprintf("UPDATE rsswidget.blogs SET `out`=%d WHERE id=%d",$r1['cnt'],$r['id']);
		mysql_query($s1) or die(mysql_error()."<br>$s1");


	}

	/*	echo '<hr>';
		// todo outカウント処理
		if($_cnt=apc_fetch("rsswdg_outlog_".$v)){
			$cntAry = unserialize($_cnt);

			foreach($cntAry as $kk=>$vv){
				$s = sprintf("UPDATE blogs SET out=out+%d WHERE id=%d",$vv,$kk);
			}
			echo $s.'<br>';
			$q = mysql_query($s);
		}

		echo '<hr>';
		// todo inカウント処理
		if($_cnt=apc_fetch("rsswdg_inlog_".$v)){
			$cntAry = unserialize($_cnt);

			foreach($cntAry as $kk=>$vv){
				$s = sprintf("UPDATE blogs SET in=in+%d WHERE id=%d",$vv,$kk);
			}
			echo $s.'<br>';
			$q = mysql_query($s);
		}*/

	echo 'deleted...<br>';
	apc_delete("rsswdg_inlog_raw_".$v);
	apc_delete("rsswdg_outlog_raw_".$v);
	apc_delete("rsswdg_inlog_all_".$v);
	//apc_delete("out_lograw_".$v);
	//apc_delete("in_lograw_".$v);
	//apc_delete("refer_".$v);

}



