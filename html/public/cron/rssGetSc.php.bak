<?php
/*
 * 取得された記事でリアルタイムポストの場合の処理
 * http://blog.9wick.com/2012/08/php-api-curl-multi/
 * copyright 2013 mirecorp. s.shinohara
 */

// WordPress の XML-RPC API
// http://codex.wordpress.org/XML-RPC_WordPress_API
// FC2 XML-RPC
// http://nekoriki.net/83

// 取得したRSSを該当WPに投稿するさいに、該当WPのデータベースにアクセスするので、ポート開放および以下のDBアカウント設定が必要
// $user = 'wpaioseo';
// $password = 'Bb8YwdfAcuaP2EPI';
//
// デバッグ用として $_GET['id']に sites.id を指定すると、該当サイトのみのデータ投稿を行うことが可能

require '../simple_html_dom.php';
include_once('../IXR_Library.php');

function __trim($str){
    // 先頭と末端の空白文字を消す
    $str = trim($str);
    $ptn = array('/\r/','/\n/','/\r\n/');
    $str = preg_replace($ptn,'',$str);
    //$str = preg_replace('/\r/', '', $str);
    //$str = preg_replace('/\n/', ' ', $str);
    // 2つ以上の空白文字を消す
    //$str = preg_replace('/\s{2,}/', ' ', $str);

    return $str;
}

//ini_set('display_erros',1);

mb_language('ja');
require('../db.php');
define("DBG",0);

$gettime = time();

function fetchMultiUrl($urls, $timeout = 10, &$errorUrls = array()) {

	$mh = curl_multi_init();

	foreach ($urls as $key => $url) {
		$conn[$key] = curl_init($url);
		curl_setopt($conn[$key], CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
		curl_setopt($conn[$key], CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($conn[$key], CURLOPT_FAILONERROR, 1);
		curl_setopt($conn[$key], CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($conn[$key], CURLOPT_MAXREDIRS, 3);

		if ($timeout) {
			curl_setopt($conn[$key], CURLOPT_TIMEOUT, $timeout);
		}

		curl_multi_add_handle($mh, $conn[$key]);
	}

	$active = null;
	do {
		$mrc = curl_multi_exec($mh, $active);
	} while ($mrc == CURLM_CALL_MULTI_PERFORM);

	while ($active and $mrc == CURLM_OK) {
		if (curl_multi_select($mh) != -1) {
			do {
				$mrc = curl_multi_exec($mh, $active);
			} while ($mrc == CURLM_CALL_MULTI_PERFORM);
		}
	}

	//データを取得
	$res = array();
	foreach ($urls as $key => $url) {
		if (($err = curl_error($conn[$key])) == '') {
			$res[$key] = curl_multi_getcontent($conn[$key]);
		} else {
			$errorUrls[$key] = $url_list[$key];
		}
		curl_multi_remove_handle($mh, $conn[$key]);
		curl_close($conn[$key]);
	}
	curl_multi_close($mh);

	return $res;
}

echo "<h2>".date('Y-m-d H:i:s')."</h2>";
// 過去の記事を削除

// サイトの設定でpostType=2のサイトのもののみ記事精査
if($_GET['id']==''){
	$siteSQL = sprintf("SELECT * FROM rsswidget.sites WHERE isactive=1 AND postType=2 AND deleted_at='0000-00-00 00:00:00'");
}else{
	$siteSQL = sprintf("SELECT * FROM rsswidget.sites WHERE isactive=1 AND id=%d AND postType=2 AND deleted_at='0000-00-00 00:00:00'",$_GET['id']);
}
$siteQ = mysql_query($siteSQL) or die(mysql_error());

// サイトごとにループ
while($site = mysql_fetch_assoc($siteQ)){

	echo '<h1 style="background-color:green">site : '.$site['name'].'</h1>';

	if($site["wphost"]=='' || $site['wpuser']=='' || $site['wppass']=='') continue;

	$blogS = sprintf("SELECT * FROM rsswidget.blogs WHERE acc='%s' AND deleted_at='0000-00-00 00:00:00'",$site['acc']);
	$blogQ = mysql_query($blogS);

	if(mysql_num_rows($blogQ)==0) continue;

	// タブーワード取得
	$tabooS = sprintf("SELECT * FROM replace_words WHERE site_acc=%d",$site['acc']);
	$tabooQ = mysql_query($tabooS);
	while($tabooR=mysql_fetch_array($tabooQ)){
		$taboo[]=array('from'=>$tabooR['from'],'to'=>$tabooR['to']);
	}

	while($blog = mysql_fetch_assoc($blogQ)){

		// movSiteが空のもの（まだ投稿していないもの）だけを動画投稿を行う
		$itemS = sprintf("SELECT id,url,imgurl,title,movid FROM rsswidget.article WHERE blogid=%d AND movSite='' AND utime>%d AND posted_at='0000-00-00 00:00:00' ORDER BY utime DESC LIMIT 10",$blog['id'],time()-3600*24);
		$itemQ = mysql_query($itemS);

		echo '<h2 style="background-color:yellow">blog : '.$blog['name']."</h2><h4>{$itemS}</h4>";

		$urls = array();

		while($item = mysql_fetch_assoc($itemQ)){
			$id = $item['id'];
			$urls[$id]=$item['url'];
			$items[$id]=$item;
		}

		// 記事データを取得
		$htmls = fetchMultiUrl($urls);

		//var_dump($items);
		//var_dump($htmls);exit;

		foreach($htmls as $item_id => $__html){

			$article = array();
			$imgurl = array();

			$html = str_get_html($__html);
			$article['body'] = '';
			$article['img'] = array();
			$article['title'] = $html->find($blog['domTitle'],0)->innertext;

			$client = new IXR_Client($site['wphost']);

			// 画像
			//$imgs = $html->find('div.article-body div.appended img');
			$imgs = $html->find($blog['domBody'].' img');
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
					echo '取得画像データ';
					var_dump($img);
					$vv->src=$img['url'];
					$imgurl[]=$img['url'];	// 2015/04/28
				}else{
					$vv->src = $file;
				}
				$article['img'][] = $file;
			}
			//var_dump($imgs);

			// body中のaタグ自体を削除（中身は残す)
			$a = $html->find($blog['domBody'].' a');
			foreach($a as $kk=>$vv){
				$vv->outertext = $vv->innertext;
			}

			$bodies = $html->find($blog['domBody'],0);
			$article['body'] .= $bodies->outertext;
			$article['body'] = preg_replace('@'.$blog['filterBody2'].'@s','',$article['body']);
			$ptn = $blog['filterBody1'];

			$article['body'] = str_replace(__trim($ptn),'',$article['body']);

			// 記事が空っぽだったらWPに投稿しない
			if($article['body']=='') continue;

			$wptitle = $article['title'];

			// 2015/04/27 以下の項目の置換
			//$items[$item_id]['title']
			//$article['body']
			foreach($taboo as $k=>$v){
				// タイトルは取得時にすでに置換しているっぽい。
				$items[$item_id]['title'] = str_replace(__trim($v['from']),$v['to'],$items[$item_id]['title']);
				$article['body'] = str_replace(__trim($v['from']),$v['to'],$article['body']);
			}

			$tpl = $site['wpdesc'];
			$tpl = str_replace('#title#',$items[$item_id]['title'],$tpl);
			$tpl = str_replace('#imgurl#',$items[$item_id]['imgurl'],$tpl);
			if($imgurl[0]!=''){
				$tpl = str_replace('#img0#',$imgurl[0],$tpl);
			}else{
				$tpl = str_replace('#img0#',$blog['image'],$tpl);
			}
			$tpl = str_replace('#url#',$items[$item_id]['url'],$tpl);
			$tpl = str_replace('#content#',$article['body'],$tpl);

			$seotitle= str_replace('#title#',$items[$item_id]['title'],$site['seotitle']);
			$seotitle = str_replace('#imgurl#',$items[$item_id]['imgurl'],$seotitle);
			$seotitle = str_replace('#url#',$items[$item_id]['url'],$seotitle);

			$seodesc = str_replace('#title#',$items[$item_id]['title'],$site['seodesc']);
			$seodesc = str_replace('#imgurl#',$items[$item_id]['imgurl'],$seodesc);
			$seodesc = str_replace('#url#',$items[$item_id]['url'],$seodesc);

			$seokeyword = $site['seokeyword'];

			//var_dump($items[$item_id]);

			//var_dump($tpl);

			echo '<h3>取得データ</h3>';
			var_dump($article);
			//exit;

			if(1){
				$status = $client->query(
				  "wp.newPost", //使うAPIを指定（wp.newPostは、新規投稿）
				  1, // blog ID: 通常は１、マルチサイト時変更
				  $site['wpuser'], // ユーザー名
				  $site['wppass'], // パスワード
				  array(
				    //'post_author' => 1, // 投稿者ID 未設定の場合投稿者名なしになる。
				    'post_status' => 'publish', // 投稿状態
				    'post_title' => $wptitle, // タイトル
				    'post_content' => $tpl, //　本文
				    //'terms_names' => array('post_tag' => array('タグ１','タグ２')),
				    //'terms' => array('category' => array($cat)),// カテゴリ追加
						'custom_fields' => array(
				      array('key' => 'aioseop_title', 'value' => $seotitle),
				      array('key' => 'aioseop_keywords', 'value' => $seokeyword),
				      array('key' => 'aioseop_description', 'value' => $seodesc)
						),
				  )
				);
				$dsn = $site['wpdbhost'];
				$user = 'wpaioseo';
				$password = 'Bb8YwdfAcuaP2EPI';

				try{
			    $dbh = new PDO("mysql:".$dsn, $user, $password);
					$sql = "update wp_postmeta set meta_key = '_aioseop_title' where meta_key = 'aioseop_title'";
					$stmt = $dbh->query($sql);
					$sql = "update wp_postmeta set meta_key = '_aioseop_keywords' where meta_key = 'aioseop_keywords'";
					$stmt = $dbh->query($sql);
					$sql = "update wp_postmeta set meta_key = '_aioseop_description' where meta_key = 'aioseop_description'";
					$stmt = $dbh->query($sql);
					$dbh = null;
				}catch (PDOException $e){
			    print('WP-DB Connection failed:'.$e->getMessage());
			    //die();
				}

				if(!$status){
				  echo('error - '.$client->getErrorCode().' : '.$client->getErrorMessage().'<br>');
				}

				$post_id = $client->getResponse(); //返り値は投稿ID

				$s1 = sprintf("UPDATE rsswidget.article SET posted_at='%s' WHERE id=%d",date('Y-m-d H:i:s'),$item_id);
				$q1 = mysql_query($s1) or die(mysql_error()."&nbsp;".$s1.'<hr>');

				echo "postID is $post_id -- post finished<hr>";
			}

		} // foreach($html as $item_id => $__html){

	} // blog

}	// site


echo "<h2>記事投稿完了</h2>";
echo '--<br>time:'.(time()-$gettime).'sec';
