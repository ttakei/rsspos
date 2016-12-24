<?php

// http://blog.livedoor.jp/kaikaihanno/
// http://streakingfc2.blog.fc2.com/

$id = $_GET['id'];

echo <<< HTML
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title></title>
	<link rel="stylesheet" href="">
</head>
<body>
HTML;

require("./db.php");
//$con = mysql_pconnect('localhost','root','jahguleth') or die ("DBERR-NC");
$s = sprintf("SELECT acc FROM rsswidget.sites WHERE isactive=1 AND deleted_at='0000-00-00 00:00:00'");
$q = mysql_query($s);
while($r=mysql_fetch_assoc($q)){
	$acc[] = $r['acc'];
}

foreach($acc as $k=>$v){
 $menu .= sprintf("<a href='./a.php?id=%s'>%s</a> | ",$v,$v);
 if($_GET['apcdel']){
 	echo "rsswdg_inlog_all_$v deleted<br>";
 	apc_delete("rsswdg_inlog_all_".$v);
 }
}

$menu .= '<style>
table {
	border-collapse: collapse;
}
table th,
table td {
	border: 1px solid #CCCCCC;
	padding: 5px 10px;
	table-layout: fixed;
	text-align: left;
}
table th {
	background-color: #FFFFFF;
}
</style>';

echo $menu.'<h3>cache削除は apcdel=1を追記</h3>';

$s1 = sprintf("SELECT url,count(*) as cnt FROM rsswidget.inalllog WHERE acc = '%s' GROUP BY url ORDER BY cnt DESC",$id);
$q1= mysql_query($s1);
//echo $s1;

$table = '<table class="table">';
while($r1=mysql_fetch_assoc($q1)){
	$table .= sprintf("<tr><td>%s</td><td>%s</td></tr>",$r1['url'],$r1['cnt']);
}
$table .= "</table><hr>";

if($id!=''){
$_cnt = apc_fetch("rsswdg_inlog_all_$id");
$cntAry = unserialize($_cnt);
$table .= '<table width=1200 class="table"><tr><td width=100>連番</td><td width=150>日時</td><td>リファ</td><td width=200>リファマッチ</td><td width=100>IP</td></tr>';
foreach($cntAry as $a){
 $i++;
 $table .= sprintf("<tr><td>%07d</td><td width=150>%s</td><td><a href='%s' target='_blank'>%s</a></td><td>%s</td><td>%s</td></tr>",$i,date('Y-m-d H:i:s',$a['utime']),$a['refer'],substr($a['refer'],0,100),$a['url'],$a['ip']);
}
}
$table .= '</table>';

echo $table."</body></html>";

