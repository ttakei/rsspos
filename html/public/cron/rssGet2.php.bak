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

ini_set('display_erros',1);

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

function curl_get_contents($url){
    $array_url = parse_url($url);

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    // 証明書認証の設定
    if ($array_url['scheme'] === "https") {
        // サーバ認証しない
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        // クライアント認証しない
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    }
    // Facebook対応
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36');
    //curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.5; en-US; rv:1.9.1.3) Gecko/20090824 Firefox/3.5.3');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);

    $result = curl_exec($ch);

    curl_close($ch);

    return $result;
}

echo '<meta charset="utf-8">';

echo "<h2>".date('Y-m-d H:i:s')."</h2>";
// 過去の記事を削除

$itemDelSql = sprintf("DELETE FROM article WHERE created_at<'%s'",date('Y-m-d H:i:s',strtotime("-2 weeks")));
mysql_query($itemDelSql);

// サイトの設定でpostType=1のサイトのもののみ記事精査
if($_GET['id']==''){
	$siteSQL = sprintf("SELECT * FROM rsswidget.sites WHERE isactive=1 AND postType=1");
}else{
	$siteSQL = sprintf("SELECT * FROM rsswidget.sites WHERE isactive=1 AND postType=1 AND id=%d",$_GET['id']);
}
$siteQ = mysql_query($siteSQL) or die(mysql_error());

// サイトごとにループ
while($site = mysql_fetch_assoc($siteQ)){

	echo '<h1 style="background-color:green">site : '.$site['name'].'</h1>';

	if($site["wphost"]=='' || $site['wpuser']=='' || $site['wppass']=='') continue;

	$blogS = sprintf("SELECT * FROM rsswidget.blogs WHERE acc='%s'",$site['acc']);
	$blogQ = mysql_query($blogS);

	if(mysql_num_rows($blogQ)==0) continue;

	while($blog = mysql_fetch_assoc($blogQ)){

		$period = 3600*24*3;
		// movSiteが空のもの（まだ投稿していないもの）だけを動画投稿を行う
		if($site['wphost']=='http://blog.fc2.com/xmlrpc.php'){
			if($site['isNeedimg']){
				$itemS = sprintf("SELECT id,url,imgurl,title,movid,tag FROM rsswidget.article WHERE movSite='' AND imgurl!='' AND blogid=%d AND posted_at='0000-00-00 00:00:00' AND utime>%d ORDER BY utime DESC LIMIT 1",$blog['id'],time()-$period);
			}else{
				$itemS = sprintf("SELECT id,url,imgurl,title,movid,tag FROM rsswidget.article WHERE movSite='' AND blogid=%d AND posted_at='0000-00-00 00:00:00' AND utime>%d ORDER BY utime DESC LIMIT 1",$blog['id'],time()-$period);
			}
		}else{
			if($site['isNeedimg']){
				$itemS = sprintf("SELECT id,url,imgurl,title,movid,tag FROM rsswidget.article WHERE movSite='' AND imgurl!='' AND blogid=%d AND posted_at='0000-00-00 00:00:00' AND utime>%d ORDER BY utime DESC LIMIT 10",$blog['id'],time()-$period);
			}else{
				$itemS = sprintf("SELECT id,url,imgurl,title,movid,tag FROM rsswidget.article WHERE movSite='' AND blogid=%d AND posted_at='0000-00-00 00:00:00' AND utime>%d ORDER BY utime DESC LIMIT 10",$blog['id'],time()-$period);
			}
		}
		$itemQ = mysql_query($itemS);

		echo '<h2 style="background-color:yellow">blog : '.$blog['name'].'</h2>';

		$urls = array();

		while($item = mysql_fetch_assoc($itemQ)){
			$id = $item['id'];
			$urls[$id]=$item['url'];
			$items[$id]=$item;
			$tags[$id]=$item['tag'];
		}
		//var_dump($tags);
		//var_dump($urls);
		//var_dump($blog);
		//var_dump($items);exit;

		// 記事データを取得
		//$html = fetchMultiUrl($urls);

		foreach($urls as $url_id => $url){
			$html[$url_id] = curl_get_contents($url);
		}

		//var_dump($html);exit;

		foreach($html as $item_id => $__html){

			include_once('../IXR_Library.php');

			$tag = '';
			//var_dump($tags[$item_id]);
			$tag = explode(",",$tags[$item_id]);
			//echo '<h3>tag</h3>';
			//var_dump($tag);
			//echo '<br>';

			$movid = '';
			$movurl = '';
			$movSite = '';
			if(preg_match("@http://flashservice.xvideos.com/embedframe/(\d+)@s",$__html,$m)){
				$movSite = 'xvideo';
			}elseif(preg_match("@http://video.fc2.com/a/content/([0-9a-zA-Z]*?)/@",$__html,$m)){
				$movSite = 'fc2';
			}elseif(preg_match("@http://video.fc2.com/ja/a/content/([0-9a-zA-Z]*?)/@",$__html,$m)){
				$movSite = 'fc2ja';
			}elseif(preg_match("@http://xhamster.com/xembed.php\?video=(\d+)@s",$__html,$m)){
				$movSite = 'xhamster';
			}elseif(preg_match("@http://embed.redtube.com/\?id=(\d+?)&@s",$__html,$m)){
				$movSite = 'redtube';
			}elseif(preg_match('@"mcd=(.+?)"@',$__html,$m)){
				$movSite = 'erovideonet';
			}elseif(preg_match('@http://www.pornhub.com/embed/(.+?)"@',$__html,$m)){
				$movSite = 'pornhub';
			}elseif(preg_match("@http://www.pipii.tv/player\?id=(\d+?)&@s",$__html,$m)){
				$movSite = 'pipii';
			}else{
				echo '<div style="color:red">'.$item_id.' is unmatched</div>';
				//$movSite = '';
				$movSite = 'none';
  			// 2015/03/19
				if($site['isNeedmov']==1){

					$s1 = sprintf("DELETE FROM rsswidget.article WHERE id=%d",$item_id);
					//echo $s1.'<br>';
					$q1 = mysql_query($s1) or print(mysql_error()."&nbsp;".$s1.'<hr>');
					//echo '動画IDなし<br>';
					continue;
				}
			}

			if(isset($m[1])){
				$movid = $m[1];
			}else{
				// 動画必須出ない場合で投稿したい場合用
				$movid = "none";
			}

			echo "$item_id / $movid / $movSite / ".$urls[$item_id]."<br>";

			// 記事データがきちんと保存されているか確認用
			//var_dump($items[$item_id]);exit;

			echo "movSite:$movSite | movid:$movid<br>";

			if($movid!='' && $movSite!=''){
				// movid,movSiteのみUPDATEする
				$query = '';
				//foreach(array('title','url','created_at','imgurl','utime','description','blogid','movid','movSite') as $blogK){
				foreach(array('movid','movSite') as $blogK){
					switch($blogK){
						case 'movid':
							$query .= sprintf("%s='%s',",$blogK,sqlesc($movid));
							break;
						case 'movSite':
							$query .= sprintf("%s='%s',",$blogK,sqlesc($movSite));
							break;
					}
				}

				$s1 = sprintf("UPDATE rsswidget.article SET %s WHERE id=%d",substr($query,0,-1),$item_id);
				echo $s1.'<br>';
				//echo sprintf("<a href='%s' target='_blank'>%s</a>",$link,$link).'<br>';
				$q1 = mysql_query($s1) or die(mysql_error()."&nbsp;".$s1.'<hr>');
			} // ($movid!='' && $movSite!='-')

		} // foreach($html as $item_id => $__html){

	} // blog

}	// site


echo "<h2>動画タグ取得完了</h2>";
echo '--<br>time:'.(time()-$gettime).'sec';
