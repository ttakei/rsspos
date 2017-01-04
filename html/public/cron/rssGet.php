<?php

/*
 * POSTするデータを決定する
 * copyright 2013 mirecorp. s.shinohara
 */

// 2015/03/19 rss_parse.incを変更したので、文字列の最後に,が入るようになっているので、その辺り大改造。

mb_language('ja');
require('../db.php');

define("DBG",0);

require_once("./magpierss/rss_fetch.inc");
define("MAGPIE_CACHE_ON",false);
define("MAGPIE_CACHE_AGE",-1);
//define("MAGPIE_CACHE_DIR", "/var/www/html/blog/cache/");
define("MAGPIE_CACHE_DIR", "./cache/");
define("MAGPIE_OUTPUT_ENCODING","UTF-8");

if($_GET['id']==''){
	$siteSQL = sprintf("SELECT * FROM rsswidget.sites WHERE isactive=1");
}else{
	$siteSQL = sprintf("SELECT * FROM rsswidget.sites WHERE isactive=1 AND id=".$_GET['id']);
}
$siteQ = mysql_query($siteSQL) or die(mysql_error());

echo '<meta charset="utf-8">';

// サイトごとにループ
while($site = mysql_fetch_assoc($siteQ)){

	/*/////////////////////////////////////////////////////////////////////////////////////////////////
	 todo: 他サイトRSS記事の取得
	//////////////////////////////////////////////////////////////////////////////////////////////////*/
	$gettime = time();

	// 認証済みのサイトのみRSS取得を行う
	$blogS = sprintf("SELECT * FROM rsswidget.blogs WHERE acc='%s'",$site['acc']);
	$blogQ = mysql_query($blogS);

	if(mysql_num_rows($blogQ)==0) continue;

	// タブーワード取得
	$tabooS = sprintf("SELECT * FROM replace_words");
	$tabooQ = mysql_query($tabooS);
	while($tabooR=mysql_fetch_array($tabooQ)){
		$taboo[$tabooR['id']]=array('from'=>$tabooR['from'],'to'=>$tabooR['to']);
	}

	while($blog = mysql_fetch_assoc($blogQ)){

		$sitename = $blog['name'];
		$blog_id = $blog['id'];
		$rss = fetch_rss($blog['rssurl']);

		printf("<h3>ID:%d / %s - %s</h3>",$blog['id'],$blog['name'],$blog['rssurl']);
		//echo '<h3> ID.'.$blog['id'].' : blogname: '.$blog['name'].'.$blog['rssurl'].' : ('.$blog['ignoreflag'].':except:'.$blog['ignorecategory'].')</h3>';

		//////////////////////////////////////////////////////////////////////
		// RSS取得

		$kijino=1;
		foreach ($rss->items as $item) {	// RSS取得

			//var_dump($item);exit;

			// 2015/03/19 タグ処理 基本FC2/WP優先
			$tag = '';
			if($item['category']!=''){
				$tag = $item['category'];
			}elseif($item['dc']['subject']!=''){
				$tag = $item['dc']['subject'];
			}

			$link = $item['link'];
			$title = mb_convert_encoding($item['title'],"UTF-8","auto");
			$title = trim(mb_convert_kana( $title, "s"));	// 空白？
			$title_org = $title;

			foreach($taboo as $k=>$v){
				$title = str_replace($v['from'],$v['to'],$title);
			}

			$flag=0;

			// 広告記事のチェック
			if (preg_match("/^PR:.+$/", $title) != 0) continue;

			//$description = mb_convert_encoding($item['description'],"UTF-8","auto");
			$description = mb_convert_encoding($item['content']['encoded'],"UTF-8","auto");
			if(strlen($description)<100){
				$description = mb_convert_encoding($item['description'],"UTF-8","auto");
				if(strlen($description)<100){
					$description = '';
				}
			}

			$timestamp = $item['date_timestamp'];
			if($timestamp == '')
				$timestamp = strtotime($item['dc']['date']);
			if($timestamp=='')
				$timestamp = strtotime($item['pubDate']);
			if($timestamp=='')	// 何も設定されてない場合は現在時刻
				$timestamp = time();

			echo "$timestamp &raquo; ";

			if($timestamp>time()) continue;	// 未来日付は取り込まない

			// 画像取得 ///////////////////////////////////////////////
			$pattern = "/<img[^<>]*src\\s*=\\s*[\"']([^\"']+)?[\"'].*>/i";
			if (false!=preg_match($pattern, $item['content']['encoded'], $matches, 0, 0)) {
				$img = $matches[1];
			}else{
				if(false!=preg_match($pattern, $item['description'], $matches)) {
					$img = $matches[1];
				}else{
					$img = '';
				}
			}

			// 同じ記事URLのチェック
			$s1 = sprintf("SELECT id FROM article WHERE url='%s' AND blogid='%s'",substr($link,0,-1),$blog_id);
			$q1 = mysql_query($s1);
			if(mysql_num_rows($q1)>0){
				echo "dupilicate URL : $link<br>";
				continue;
			}else{
				 echo "<a href='$link' target='_blank'>$title</a>";
				$query = '';
				foreach(array('title','url','created_at','imgurl','utime','description','blogid','title_org','tag') as $blogK){
					switch($blogK){
						case 'utime':
							$query .= sprintf("%s='%s',",$blogK,time());
						case 'article_id':
							// ★あとで処理が必要
							break;
						case 'blogid':
							$query .= sprintf("%s='%s',",$blogK,$blog_id);
							break;
						case 'title':
							$title = substr($title,0,-1);
							$query .= sprintf("%s='%s',",$blogK,sqlesc($title));
							break;
						case 'title_org':
							$title_org = substr($title_org,0,-1);
							$query .= sprintf("%s='%s',",$blogK,sqlesc($title_org));
							break;
						case 'created_at':	// 投稿時刻
							$query .= sprintf("%s='%s',",$blogK,sqlesc(date("Y-m-d H:i:s",$timestamp)));
							break;
						case 'url':
							$link = substr($link,0,-1);
							$query .= sprintf("%s='%s',",$blogK,sqlesc($link));
							break;
						case 'imgurl':
							$query .= sprintf("%s='%s',",$blogK,sqlesc($img));
							break;
						case 'tag':
							$tag = substr($tag,0,-1);
							$query .= sprintf("%s='%s',",$blogK,sqlesc($tag));
							break;
						case 'description':
							$description = substr($description,0,-1);
							$query .= sprintf("description='%s',",sqlesc($description));
							break;
					}
				}

				$s1 = sprintf("INSERT INTO rsswidget.article SET %s",$query);
				$s1 = substr($s1,0,-1);
				//echo sprintf("<a href='%s' target='_blank'>%s</a>",$link,$link).'<br>';
				$q1 = mysql_query($s1) or die(mysql_error()."&nbsp;".$s1.'<hr>');

				// 2014/04/22 追加
				$id = mysql_insert_id();

				echo $id.'<br>';

				if($blog['image']==1){
					$json = file_get_contents("http://ajax.googleapis.com/ajax/services/search/images?v=1.0&rsz=4&q=".urlencode($title));
					$jsonD = (json_decode($json,true));
					//var_dump($jsonD['respondData']['results']);

					foreach($jsonD['responseData']['results'] as $v){
						if($v['width']>200){
							printf("<img src='%s' width=200><br>",$v['url']);
							$docroot = $_SERVER['DOCUMENT_ROOT'];
							$file = file_get_contents($v['url']);
							//if(strlen($file)<1000) break;
							file_put_contents($docroot.'/images/tmp',$file);
							$exeCmd = sprintf("/usr/bin/convert -resize 200x %s %s",$docroot.'/images/tmp',$docroot.'/images/'.$id);
							echo "Command : $exeCmd <br>";
							shell_exec($exeCmd);
							break;
						}
					}
				}

			}
		}	// rss
		$kijino++;
	} // blog
	echo "<h2>記事取得完了</h2>";
	echo '--<br>time:'.(time()-$gettime).'sec';

}	// $site['blogId']分だけループ

if(DBG){
	$s = sprintf("UPDATE darticle_item SET ocnt=2+RAND()*80");
	$q = mysql_query($s);
	$s = sprintf("UPDATE blogManage SET BMocnt=50+RAND()*200");
	$q = mysql_query($s);
	$s = sprintf("UPDATE blogManage SET BMicnt=50+RAND()*200");
	$q = mysql_query($s);
}

