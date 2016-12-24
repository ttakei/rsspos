<?php

// $id サイトID
// $p　パーツID
// $s  スタート番号
// $limit 表示個数
// $selid 表示ブログID

header('Content-Type: text/javascript');
//header("Content-type: application/x-javascript");
//printf("document.write('alert(\"hoge\")');");
//exit;

$id = $_GET['id'];
$Gp = $_GET['p'];
$Gs = ($_GET['start']=='')?0:$_GET['start'];
$Glimit = ($_GET['limit']=='')?50:$_GET['limit'];
$Gselid = $_GET['selid'];
$Goffset = ($_GET['offset']=='')?1:$_GET['offset']; // 2014/05/13
$Gm = ($_GET['m']=='')?'inrank':$_GET['m'];

$dbg = ($_GET['dbg']=='')?0:1;
//if($_SERVER['REMOTE_HOST']=='180.19.234.71' && !$tmp = apc_get(sprintf("parts-%s-parts%d-start%d-limit%d-selid%s",$id,$Gp,$Gs,$Glimit,$Gselid))){
//if($_SERVER['REMOTE_ADDR']=='180.19.234.71'){

$key = sprintf("%s-parts%d-%s-start%d-limit%d-selid%s-offset%d",$id,$Gp,$Gm,$Gs,$Glimit,$Gselid,$Goffset);
if($dbg==0){
	if($tmp = apc_fetch($key)){
		echo $tmp;
		exit;
	}
}else{
	//apc_delete($key);
}

require("db.php");

$Gm = sqlesc($_GET['m']);

$id = sqlesc($_GET['id']);
$Gp = sqlesc($_GET['p']);
$Gs = ($_GET['start']=='')?0:sqlesc($_GET['start']);
$Glimit = ($_GET['limit']=='')?50:$_GET['limit'];
$Glimit = sqlesc($Glimit);
$Gselid = sqlesc($_GET['selid']);

if(1 || !$tmp = apc_fetch("rsswdg_parts_".$id)){
	$s = sprintf("SELECT * FROM rsswidget.parts WHERE acc='%s'",$id);
	$q = mysql_query($s);
	while($r=mysql_fetch_assoc($q)){
		$key = $r['id'];
		$parts[$key]=$r['tpl'];
	}
	apc_store("rsswdg_parts_".$id,$parts,600);
}else{
	$parts = unserialize($tmp);
}

// パーツチェック
preg_match("@(.*?)#loop(.*?)#endloop(.*?)$@s",$parts[$Gp],$m);
if(count($m)>1){
	$parts[$Gp] = $m[2];
	$header  = $m[1];
	$footer = $m[3];
}else{
	$header ='';
	$footer = '';
}

// 2015/01/26 パーツの広告を取得
$s = sprintf("SELECT adline1,adtpl1 FROM rsswidget.parts WHERE id=%d AND adline1!=''",$Gp);
$q = mysql_query($s);
if(mysql_num_rows($q)!=0){
	$r = mysql_fetch_assoc($q);
	$rk = $r['adline1'];
	if($rk=='#') $rk = mt_rand(0,$Glimit-1);
	$adparts[$rk] = $r['adtpl1'];
}
$s = sprintf("SELECT adline2,adtpl2 FROM rsswidget.parts WHERE id=%d AND adline2!=''",$Gp);
$q = mysql_query($s);
if(mysql_num_rows($q)!=0){
	$r = mysql_fetch_assoc($q);
	$rk = $r['adline2'];
	if($rk=='#') $rk = mt_rand(0,$Glimit-1);
	$adparts[$rk] = $r['adtpl2'];
}
$s = sprintf("SELECT adline3,adtpl3 FROM rsswidget.parts WHERE id=%d AND adline3!=''",$Gp);
$q = mysql_query($s);
if(mysql_num_rows($q)!=0){
	$r = mysql_fetch_assoc($q);
	$rk = $r['adline3'];
	if($rk=='#') $rk = mt_rand(0,$Glimit-1);
	$adparts[$rk] = $r['adtpl3'];
}
if($dbg) var_dump($adparts);

if($Gm=='id'){

	$s = sprintf("SELECT * FROM blogs WHERE acc='%s' AND deleted_at='0000-00-00 00:00:00' AND rssurl!='' AND  id IN (%s)",$id,$Gselid);
	$q = mysql_query($s);

	while($r=mysql_fetch_assoc($q)){
		$s1 = sprintf("SELECT a.url,a.imgurl,a.title,a.blogid,b.name,b.image,b.title as btitle,b.in,b.out,a.id,b.siteurl,b.url as burl FROM rsswidget.article as a LEFT JOIN blogs as b ON a.blogid=b.id WHERE blogid='%s' ORDER BY a.created_at DESC,a.id DESC LIMIT %d",$r['id'],$Glimit);
		//echo $s1;
		$q1 = mysql_query($s1) or die($s1);

		$i=0;
		while($article = mysql_fetch_assoc($q1)){
			// 2015/01/26
			// 指定行数の場合に、$hoge[]に広告タグを追加
			foreach($adparts as $kkk=>$vvv){
				if($i==$kkk){
					$hoge[]=$vvv;
				}
			}

			$tpl = $parts[$Gp];
			$tpl = str_replace("#blogid#",$article['blogid'],$tpl);

			$tpl = str_replace("#name#",$article['name'],$tpl);

			if($article['btitle']!=''){
				$tpl = str_replace("#title#",$article['btitle'],$tpl);
			}else{
				$tpl = str_replace("#title#",$article['title'],$tpl);
			}

			$ptn = "@#title:(\d+?)#@";
			preg_match($ptn,$tpl,$m);
			if($article['btitle']!=''){
				$tpl = str_replace("#title:".$m[1]."#",mb_strimwidth($article['btitle'],0,$m[1],'…','UTF-8'),$tpl);
			}else{
				$tpl = str_replace("#title:".$m[1]."#",mb_strimwidth($article['title'],0,$m[1],'…','UTF-8'),$tpl);
			}

			if($article['burl']!=''){
				$tpl = str_replace("#url#",$article['burl'],$tpl);
			}else{
				$tpl = str_replace("#url#",$article['url'],$tpl);
			}
			$tpl = str_replace("#blogurl#",$article['siteurl'],$tpl);

			if($article['image']==1){
				$tpl = str_replace("#imgurl#",'http://'.$_SERVER['HTTP_HOST'].'/images/'.$article['id'],$tpl);
			}elseif($article['image']!=''){
				$_tmp = explode(",",$article['image']);
				$_keys = array_rand($_tmp, 1);
				//$tpl = str_replace("#imgurl#",$article['image'],$tpl);
				$tpl = str_replace('#imgurl#',$_tmp[$_keys],$tpl);
			}else{
				$tpl = str_replace("#imgurl#",$article['imgurl'],$tpl);
			}

			$tpl = str_replace("#in#",$article['in'],$tpl);
			$
			$tpl = str_replace("#no#",$i+1,$tpl);
			$tpl = str_replace("#no0#",$i,$tpl);

			$hoge[] = $tpl;
			$i++;
		}

	}

}elseif($Gm=='mid'){

	$s = sprintf("SELECT * FROM blogs WHERE acc='%s' AND deleted_at='0000-00-00 00:00:00' AND rssurl!='' AND  id IN (%s)",$id,$Gselid);
	$q = mysql_query($s);

	while($r=mysql_fetch_assoc($q)){
		$s1 = sprintf("SELECT a.url,a.imgurl,a.title,a.blogid,b.name,b.image,b.title as btitle,b.in,b.out,a.id,b.siteurl,b.url as burl FROM rsswidget.article as a LEFT JOIN blogs as b ON a.blogid=b.id WHERE blogid='%s' ORDER BY a.created_at DESC,a.id DESC LIMIT %d,%d",$r['id'],$Goffset,$Glimit);
		//echo $s1;
		$q1 = mysql_query($s1);

		$i=0;

		while($article = mysql_fetch_assoc($q1)){
		// 2015/01/26
		// 指定行数の場合に、$hoge[]に広告タグを追加
		foreach($adparts as $kkk=>$vvv){
			//if($dbg) echo "<h4>".$i."/".$kkk."</h4>";
			if($i++==$kkk){
				$hoge[]=$vvv;
			}
		}
			$tpl = $parts[$Gp];
			$tpl = str_replace("#blogid#",$article['blogid'],$tpl);

			$tpl = str_replace("#name#",$article['name'],$tpl);

			if($article['btitle']!=''){
				$tpl = str_replace("#title#",$article['btitle'],$tpl);
			}else{
				$tpl = str_replace("#title#",$article['title'],$tpl);
			}

			$ptn = "@#title:(\d+?)#@";
			preg_match($ptn,$tpl,$m);
			if($article['btitle']!=''){
				$tpl = str_replace("#title:".$m[1]."#",mb_strimwidth($article['btitle'],0,$m[1],'…','UTF-8'),$tpl);
			}else{
				$tpl = str_replace("#title:".$m[1]."#",mb_strimwidth($article['title'],0,$m[1],'…','UTF-8'),$tpl);
			}

			if($article['burl']!=''){
				$tpl = str_replace("#url#",$article['burl'],$tpl);
			}else{
				$tpl = str_replace("#url#",$article['url'],$tpl);
			}
			$tpl = str_replace("#blogurl#",$article['siteurl'],$tpl);

			if($article['image']==1){
				$tpl = str_replace("#imgurl#",'http://'.$_SERVER['HTTP_HOST'].'/images/'.$article['id'],$tpl);
			}elseif(preg_match('@^http://@',$article['image'])){
				$_tmp = explode(",",$article['image']);
				$_keys = array_rand($_tmp, 1);
				//$tpl = str_replace("#imgurl#",$article['image'],$tpl);
				$tpl = str_replace('#imgurl#',$_tmp[$_keys],$tpl);
				$tpl = str_replace("#imgurl#",$article['image'],$tpl);
			}else{
				$tpl = str_replace("#imgurl#",$article['imgurl'],$tpl);
			}

			$tpl = str_replace("#in#",$article['in'],$tpl);

			$tpl = str_replace("#no#",$i+1,$tpl);
			$tpl = str_replace("#no0#",$i,$tpl);

			$hoge[] = $tpl;
		}
		//$i++;

	}

}else{

	if($Gm=='outrate'){
		$s = sprintf("SELECT * FROM rsswidget.blogs WHERE acc='%s' AND deleted_at='0000-00-00 00:00:00' AND rssurl!='' ORDER BY `in`/`out` asc LIMIT %d,%d",$id,$Gs,$Glimit);
	}else{
		$s = sprintf("SELECT * FROM rsswidget.blogs WHERE acc='%s' AND deleted_at='0000-00-00 00:00:00' AND rssurl!='' ORDER BY `in` desc LIMIT %d,%d",$id,$Gs,$Glimit);
	}

	$q = mysql_query($s);

	$i=0;
	while($r=mysql_fetch_assoc($q)){
		// 2015/01/26
		// 指定行数の場合に、$hoge[]に広告タグを追加
		foreach($adparts as $kkk=>$vvv){
			if($i==$kkk){
				$hoge[]=$vvv;
			}
		}

		$s1 = sprintf("SELECT a.url,a.imgurl,a.title,a.blogid,b.name,b.image,b.title as btitle,b.in,b.out,a.id,b.siteurl,b.url as burl FROM rsswidget.article as a LEFT JOIN blogs as b ON a.blogid=b.id WHERE blogid=%d ORDER BY a.created_at DESC,a.id DESC LIMIT %d,1",$r['id'],$Goffset);
		//echo $s1;
		$q1 = mysql_query($s1);
		$article = mysql_fetch_assoc($q1);
		//var_dump($article);

		$tpl = $parts[$Gp];
		$tpl = str_replace("#blogid#",$article['blogid'],$tpl);

		$tpl = str_replace("#name#",$article['name'],$tpl);

		if($article['btitle']!=''){
			$tpl = str_replace("#title#",$article['btitle'],$tpl);
		}else{
			$tpl = str_replace("#title#",$article['title'],$tpl);
		}

		$ptn = "@#title:(\d+?)#@";
		preg_match($ptn,$tpl,$m);
		if($article['btitle']!=''){
			$tpl = str_replace("#title:".$m[1]."#",mb_strimwidth($article['btitle'],0,$m[1],'…','UTF-8'),$tpl);
		}else{
			$tpl = str_replace("#title:".$m[1]."#",mb_strimwidth($article['title'],0,$m[1],'…','UTF-8'),$tpl);
		}

		if($article['burl']!=''){
			$tpl = str_replace("#url#",$article['burl'],$tpl);
		}else{
			$tpl = str_replace("#url#",$article['url'],$tpl);
		}
		$tpl = str_replace("#blogurl#",$article['siteurl'],$tpl);

		if($article['image']==1){
			$tpl = str_replace("#imgurl#",'http://'.$_SERVER['HTTP_HOST'].'/images/'.$article['id'],$tpl);
		}elseif(preg_match('@^http://@',$article['image'])){
			$_tmp = explode(",",$article['image']);
			$_keys = array_rand($_tmp, 1);
			//$tpl = str_replace("#imgurl#",$article['image'],$tpl);
			$tpl = str_replace('#imgurl#',$_tmp[$_keys],$tpl);
		}else{
			$tpl = str_replace("#imgurl#",$article['imgurl'],$tpl);
		}

		$tpl = str_replace("#in#",$article['in'],$tpl);

		$tpl = str_replace("#no#",$i+1,$tpl);
		$tpl = str_replace("#no0#",$i,$tpl);

		$hoge[] = $tpl;
		$i++;

	}

}


$moge = $header;
foreach($hoge as $k=>$v){
	$moge .= $v;
}
$moge .= $footer;

$moge = addcslashes($moge,"'");
$moge = str_replace(array("\r\n","\r","\n"), '', $moge);
$out = sprintf("document.write('%s');",$moge);
//$moge = str_replace("\\'","'",$moge);
//$out = sprintf("document.write(\"%s\");",str_replace('"',"&quot;",$moge));

// $id サイトID
// $p　パーツID
// $s  スタート番号
// $limit 表示個数
// $selid 表示ブログID

apc_store(sprintf("%s-parts%d-%s-start%d-limit%d-selid%s-offset%d",$id,$Gp,$Gm,$Gs,$Glimit,$Gselid,$Goffset),$out,60*5);

echo $out;
