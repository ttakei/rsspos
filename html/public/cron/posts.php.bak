<?php
/*
 * reserved_atが登録された記事を投稿するプログラム
 * copyright 2013 mirecorp. s.shinohara
 */

// researved_at に日時が登録された記事を抽出し、現時刻が予約時間を超えていたら該当ブログに投稿する。
// 投稿後は、researved_at を 空にする。or posted_at!=0000-00-00 00:00:00のものを選ぶ。

// 取得したRSSを該当WPに投稿するさいに、該当WPのデータベースにアクセスするので、ポート開放および以下のDBアカウント設定が必要
// $user = 'wpaioseo';
// $password = 'Bb8YwdfAcuaP2EPI';
//
// デバッグ用として $_GET['id']に sites.id を指定すると、該当サイトのみのデータ投稿を行うことが可能
include_once('../IXR_Library.php');

ini_set('display_erros',1);

mb_language('ja');
require('../db.php');
define("DBG",0);

$gettime = time();

echo '<meta charset="utf-8">';

echo "<h2>".date('Y-m-d H:i:s')."</h2>";

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

			if($site['wphost']=='http://blog.fc2.com/xmlrpc.php'){
					if($site['isNeedimg']){
							$itemS = sprintf("SELECT * FROM rsswidget.article WHERE researved_at!='0000-00-00 00:00:00' AND imgurl!='' AND blogid=%d AND posted_at='0000-00-00 00:00:00' ORDER BY utime LIMIT 1",$blog['id']);
					}else{
							$itemS = sprintf("SELECT * FROM rsswidget.article WHERE researved_at!='0000-00-00 00:00:00' AND blogid=%d AND posted_at='0000-00-00 00:00:00' ORDER BY utime DESC LIMIT 1",$blog['id']);
					}
			}else{
					if($site['isNeedimg']){
							$itemS = sprintf("SELECT * FROM rsswidget.article WHERE researved_at!='0000-00-00 00:00:00' AND imgurl!='' AND blogid=%d AND posted_at='0000-00-00 00:00:00' ORDER BY utime DESC LIMIT 1",$blog['id']);
					}else{
							$itemS = sprintf("SELECT * FROM rsswidget.article WHERE researved_at!='0000-00-00 00:00:00' AND blogid=%d AND posted_at='0000-00-00 00:00:00' ORDER BY utime DESC LIMIT 1",$blog['id']);
					}
			}
			$itemQ = mysql_query($itemS);

			echo $itemS;

			echo '<h2 style="background-color:yellow">blog : '.$blog['name'].'</h2>';

			if(mysql_num_rows($itemQ)>0){
				while($item = mysql_fetch_assoc($itemQ)){
					$id = $item['id'];
					$tag = explode(",",$item['tag']);
					// var_dump($item['tag']);
					// echo '<hr>';
					// var_dump($tag);
					// echo '<hr>';
					//var_dump($item);
					//echo '<hr>';
					$movSite = $item['movSite'];
					//$urls[$id]=$item['url'];
					//$items[$id]=$item;
					//$tags[$id]=$item['tag'];


					// 記事本文とseotitle,seodescを置換
					// 2016/11/06 rewriteがからの場合は、従来どおり、titleを利用。
					$wptitle = ($item['title_rewrite']=='')?$item['title']:$item['title_rewrite'];
					// $wptitle = str_replace('#title#',$item['title'],$site['wptitle']);
					// $wptitle = str_replace('#imgurl#',$item['imgurl'],$wptitle);
					// $wptitle = str_replace('#url#',$item['url'],$wptitle);

					// $item['movSite']によって$tplは代わる
					$tpl = $site[$movSite];
					$tpl = str_replace('#title#',$wptitle,$tpl);
					$tpl = str_replace('#imgurl#',$item['imgurl'],$tpl);
					$tpl = str_replace('#url#',$item['url'],$tpl);
					$tpl = str_replace('#movid#',$item['movid'],$tpl);
					$tpl = str_replace('#movSite#',$item['movSite'],$tpl);

					$seotitle = $item['seo_title'];
					// $seotitle= str_replace('#title#',$item['title'],$site['seotitle']);
					// $seotitle = str_replace('#imgurl#',$item['imgurl'],$seotitle);
					// $seotitle = str_replace('#url#',$item['url'],$seotitle);

					$seodesc = $item['seo_desc'];
					// $seodesc = str_replace('#title#',$item['title'],$site['seodesc']);
					// $seodesc = str_replace('#imgurl#',$item['imgurl'],$seodesc);
					// $seodesc = str_replace('#url#',$item['url'],$seodesc);

					$seokeyword = $item['seo_keyword'];
					//$seokeyword = str_replace('#tag#',$item['tag'],$site['seokeyword']);

					$client = new IXR_Client($site['wphost']);

					$postDate = new IXR_Date(strtotime($item['researved_at']));
					//var_dump($postDate);exit;

					if($site['wphost']=='http://blog.fc2.com/xmlrpc.php'){
						echo "<h2>FC2</h2>";

						// カテゴリ一覧
						$status = $client->query(
							'mt.getCategoryList',
							'MyBlog',
							$site['wpuser'],
							$site['wppass']
						);
						$cats = $client->getResponse();
						var_dump($cats);

						$status = $client->query(
							'metaWeblog.newPost',
							'MyBlog',
							$site['wpuser'], //ブログID
							$site['wppass'], //パスワード
							array('title' =>$wptitle,'description' => $tpl,'dateCreated' => $postDate),
							2 //  0:下書き 1:公開 2:予約投稿
						);
						$range = explode(",",$site['catid']);
						$catAry = range($range[0],$range[1]);
						$catid = array_rand($catAry);
						if(!$status){
							echo 'Something went wrong - '.$client->getErrorCode().' : '.$client->getErrorMessage();
						} else {
							$post_id = $client->getResponse(); //返り値は投稿ID
							$status2 = $client->query(
								'mt.setPostCategories',
								$post_id,
								$site['wpuser'], //ブログID
								$site['wppass'], //パスワード
								array(array('categoryId' =>$catAry[$catid],'isPrimary' => 1))
								);
						}
					}else{
						echo "<h2>WP</h2>";
						if(0){
							// カテゴリ取得
							$status = $client->query(
								"wp.getTerms",
								1,
								$site['wpuser'],
								$site['wppass'],
								'category'
							);

							// タクソノミー
							if(!$status){
								echo('error - '.$client->getErrorCode().' : '.$client->getErrorMessage().'<br>');
							}else{
								foreach($client->getResponse() as $k=>$v){
									if($v['slug']==$movSite)
										$wpCatId = $v['term_id'];
								}
							}
						}
						// カテゴリー
						// if($item->category!='' AND is_array(explode(',',$item->category))){

						// }
						//$wpCatId = ($item['category']=='')?[]:explode(',',$item['category']);
						if($item['category']!=''){
							$wpCat = explode(',',$item['category']);
						}
						//var_dump($wpCat); exit;

						if(isset($wpCat)){
							$status = $client->query(
								"wp.newPost", //使うAPIを指定（wp.newPostは、新規投稿）
								1, // blog ID: 通常は１、マルチサイト時変更
								$site['wpuser'], // ユーザー名
								$site['wppass'], // パスワード
								array(
									//'post_author' => 1, // 投稿者ID 未設定の場合投稿者名なしになる。
									'post_status' => 'future', // 投稿状態
									'post_date' => $postDate,
									'post_title' => $wptitle, // タイトル
									'post_content' => $tpl, //　本文
									'terms' => array('category' => $wpCat),// カテゴリ
									'terms_names' => array('post_tag' => $tag),	// タグ
									//'terms_names' => array('post_tag' => array('タグ１','タグ２')),
									'custom_fields' => array(
										array('key' => '_aioseop_title', 'value' => $seotitle),
										array('key' => '_aioseop_keywords', 'value' => $seokeyword),
										array('key' => '_aioseop_description', 'value' => $seodesc)
									),
								)
							);
						}else{
							$status = $client->query(
								"wp.newPost", //使うAPIを指定（wp.newPostは、新規投稿）
								1, // blog ID: 通常は１、マルチサイト時変更
								$site['wpuser'], // ユーザー名
								$site['wppass'], // パスワード
								array(
									//'post_author' => 1, // 投稿者ID 未設定の場合投稿者名なしになる。
									'post_status' => 'future', // 投稿状態
									'post_date' => $postDate,
									'post_title' => $wptitle, // タイトル
									'post_content' => $tpl, //　本文
									//'terms' => array('category' => $wpCat),// カテゴリ
									'terms_names' => array('post_tag' => $tag),	// タグ
									//'terms_names' => array('post_tag' => array('タグ１','タグ２')),
									//'terms' => array('category' => array($wpCatId)),// カテゴリ追加
									'custom_fields' => array(
										array('key' => '_aioseop_title', 'value' => $seotitle),
										array('key' => '_aioseop_keywords', 'value' => $seokeyword),
										array('key' => '_aioseop_description', 'value' => $seodesc)
									),
								)
							);
						}

						// All in one seo用投稿
						if(0){
							$dsn = $site['wpdbhost'];
							$user = 'wpaioseo';
							$password = 'Bb8YwdfAcuaP2EPI';

							// mysql:dbname=test;host=localhost;user=xxx;pass=yyy
							try{
								$dbh = new PDO($dsn);
									$sql = "update wp_postmeta set meta_key = '_aioseop_title' where meta_key = 'aioseop_title'";
									$stmt = $dbh->query($sql);
									$sql = "update wp_postmeta set meta_key = '_aioseop_keywords' where meta_key = 'aioseop_keywords'";
									$stmt = $dbh->query($sql);
									$sql = "update wp_postmeta set meta_key = '_aioseop_description' where meta_key = 'aioseop_description'";
									$stmt = $dbh->query($sql);
									$dbh = null;
							}catch (PDOException $e){
								printf("dsn:%s",$dsn);
								print('all in one seo Connection failed:'.$e->getMessage().'<hr>');
								//die();
							}
						}

						// 投稿が終わったら posted_atに日時を入れる or researved_at を消す
						$post_id = $client->getResponse(); //返り値は投稿ID

						$s1 = sprintf("UPDATE article SET posted_at='%s' WHERE id=%d",date('Y-m-d H:i:s'),$item['id']);
						$q1 = mysql_query($s1) or die(mysql_error()."&nbsp;".$s1.'<hr>');

						if(!$status){
							echo('error - '.$client->getErrorCode().' : '.$client->getErrorMessage().'<br>');
						}else{

							echo "postID is $post_id -- post finished<hr>";

							// アイキャッチ用投稿
							// 2015/03/18 画像投稿＆アイキャッチ設定
							//var_dump($items);
							//var_dump($items[$item_id]['imgurl']);exit;
							if($item['imgurl']!='' && $site['isEyecatch']==1){
								$aaa = file_get_contents($item['imgurl']);
								list($micro, $Unixtime) = explode(" ", microtime());
								$fname = date('YmdHis').str_replace('.','_',$micro).".jpg";
								$file = "timg/".$fname;
								if(!file_put_contents("timg/".$fname,$aaa)){
										die("file put error");
								}

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
								echo '<h3>img post</h3>';
								var_dump($img);

								$status3 = $client->query(
									"wp.editPost",
									1,
									$site['wpuser'], // ユーザー名
									$site['wppass'], // パスワード
									$post_id,
									array("post_thumbnail" => $img['id'])
								);
								$thumb = $client->getResponse();
								echo '<h3>eye catch</h3>';
								var_dump($thumb);
								unlink($file);  // ファイルを削除しておく
							} // アイキャッチ投稿
						}

					}
				} // 投稿記事ごと

			}else{
				echo '投稿する記事がありません<br>';
			}

		} // blog
}   // site


echo "<h2>記事投稿完了</h2>";
echo '--<br>time:'.(time()-$gettime).'sec';
