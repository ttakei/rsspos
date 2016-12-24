<?php

// article['body']中の画像タグはすべてファイル保存してファイルをアップロードする必要がある。
// body中のaタグは削除
// 同一URLは取得しない
// 置換処理

//die(getcwd());

define("WP_POST",1);

require 'simple_html_dom.php';

$site['wphost'] = 'http://xn--eckybbey5c6pxbxe6593cc83a0s4blvye.jp/xmlrpc.php';
$site['wpuser'] = 'lis';
$site['wppass'] = 'modmodmod';

include_once('./IXR_Library.php');

require_once("./cron/magpierss/rss_fetch.inc");
define("MAGPIE_CACHE_ON",false);
define("MAGPIE_CACHE_AGE",-1);
define("MAGPIE_CACHE_DIR", "./cache/");
define("MAGPIE_OUTPUT_ENCODING","UTF-8");

// RSSから記事URL取得して、記事内容から情報取得する

function __trim($str){
    // 先頭と末端の空白文字を消す
    $str = trim($str);
    $str = preg_replace('/\r/', '', $str);
    $str = preg_replace('/\n/', ' ', $str);
    // 2つ以上の空白文字を消す
    //$str = preg_replace('/\s{2,}/', ' ', $str);

    return $str;
}

$rssurl = 'http://xn--cckp8a9k3bxc6cb2378dfiwbp23i.com/feed/';

$rss = fetch_rss($rssurl);

foreach ($rss->items as $item) {	// RSS取得

	$link = $item['link'];
	$title = mb_convert_encoding($item['title'],"UTF-8","auto");
	$title = trim(mb_convert_kana( $title, "s"));	// 空白？
	$title_org = $title;

/*	foreach($taboo as $k=>$v){
		$title = str_replace($v['from'],$v['to'],$title);
	}

*/
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

	// $link
	// $title_org
	// $description
	// $timestamp
	// $img

	printf("link:%s<br>title:%s<br>time:%s<br>img:%s<hr>",$link,$title_org,$timestamp,$img);

	//$html = file_get_html('http://xn--cckp8a9k3bxc6cb2378dfiwbp23i.com/');
	$html = file_get_html($link);

	$article['body'] = '';
	$article['img'] = array();

	$article['title'] = $html->find('h1.article-title',0)->innertext;

/*
	$client = new IXR_Client($site['wphost']);
	$fname = "./timg/201503061610320_52560500.jpg";
	$imgInfo = getimagesize($fname);
	$type = $imgInfo['mime'];
	$bits = new IXR_Base64(file_get_contents($fname));
	$status2 = $client->query(
	  "wp.uploadFile",
	  1,
	  $site['wpuser'], // ユーザー名
	  $site['wppass'], // パスワード
	  array(
	    'name' => "aaa.jpg",
	    'type' => $type,
	    'bits' => $bits,
	    'overwrite' => false,
	    //'post_id' => $post_id
	  )
	);
	$img = $client->getResponse();
	var_dump($img);
	exit;
*/

	$client = new IXR_Client($site['wphost']);

	// body中の画像はファイル保存し、パス変更を行う
	$imgs = $html->find('div.article-body div.appended img');
	foreach($imgs as $kk=>$vv){
		$aaa = file_get_contents($vv->src);
		list($micro, $Unixtime) = explode(" ", microtime());
		$fname = date('YmdHis').str_replace('.','_',$micro).".jpg";
		$file = "timg/".$fname;
		if(!file_put_contents("timg/".$fname,$aaa)){
			die("error");
		}

		if(1){
			$imgInfo = getimagesize($file);
			$type = $imgInfo['mime'];

			$bits = new IXR_Base64(file_get_contents($file));
			$status2 = $client->query(
			  "wp.uploadFile",
			  1,
			  $site['wpuser'], // ユーザー名
			  $site['wppass'], // パスワード
			  array(
			    'name' => $fname,
			    'type' => $type,
			    'bits' => $bits,
			    'overwrite' => false,
			    //'post_id' => $post_id
			  )
			);
			$img = $client->getResponse();
			var_dump($img);
			$vv->src=$img['url'];
		}else{
			$vv->src = $file;
		}
		$article['img'][] = $file;
	}

	// body中のaタグ自体を削除（中身は残す)
	$a = $html->find('div.article-body div.appended a');
	foreach($a as $kk=>$vv){
		$vv->outertext = $vv->innertext;
	}


	if(0){
		$bodies = $html->find('div.article-body div.appended');
		foreach($bodies as $kk=>$vv){
			$article['body'] .= $vv->outertext;
		}
	}else{
		$bodies = $html->find('div.article-body div.article-body-inner',0);
		$article['body'] .= $bodies->outertext;
		//$article['body'] = preg_replace('@<div style="width:300px; float:left;">.*?</div>@s','',$article['body']);
		//$article['body'] = preg_replace('@<div style="width:300px; float:left; margin-left:20px;">.*?</div>@s','',$article['body']);
		$article['body'] = preg_replace('@<dl class="article-tags">.+?</dl>@s','',$article['body']);
	$ptn ='<div style="width:300px; float:left;">
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- 【乖離性MAPC】300_250 -->
<ins class="adsbygoogle"
     style="display:inline-block;width:300px;height:250px"
     data-ad-client="ca-pub-1921072420486021"
     data-ad-slot="2027959998"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
</div>

<div style="width:300px; float:left; margin-left:20px;">
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- 【乖離性MAPC】300_250 -->
<ins class="adsbygoogle"
     style="display:inline-block;width:300px;height:250px"
     data-ad-client="ca-pub-1921072420486021"
     data-ad-slot="2027959998"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
</div>
</div>';

		//echo "<hr>".__trim($ptn)."<hr>";
		$article['body'] = str_replace(__trim($ptn),'',$article['body']);
	}

	//var_dump($article);
	//echo '<hr>';

	// 記事が空っぽだったらWPに投稿しない
	if($article['body']=='') continue;

	if(1){
		$tpl = $article['body'];
		// 実記事投稿
		$status = $client->query(
		  "wp.newPost", //使うAPIを指定（wp.newPostは、新規投稿）
		  1, // blog ID: 通常は１、マルチサイト時変更
		  $site['wpuser'], // ユーザー名
		  $site['wppass'], // パスワード
		  array(
		    //'post_author' => 1, // 投稿者ID 未設定の場合投稿者名なしになる。
		    'post_status' => 'publish', // 投稿状態
		    'post_title' => $title, // タイトル
		    'post_content' => $tpl, //　本文
		    //'terms' => array('category' => array($cat)),// カテゴリ追加
	/*
				'custom_fields' => array(
		      array('key' => 'aioseop_title', 'value' => $seotitle),
		      array('key' => 'aioseop_keywords', 'value' => $seokeyword),
		      array('key' => 'aioseop_description', 'value' => $seodesc)
				),
	*/
		  )
		);

		if(!$status){
		  echo('error - '.$client->getErrorCode().' : '.$client->getErrorMessage().'<br>');
		}

		$post_id = $client->getResponse(); //返り値は投稿ID

		var_dump($post_id);
	}
	echo '<hr>';

}

