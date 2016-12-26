<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

//Route::when('/', 'myauth');
//*/15 * * * * curl http://rssweb.net/cron/cnt.php
//5,35 * * * * curl http://rssweb.net/cron/rssGet.php;curl http://rssweb.net/cron/rssGet2.php

View::composer('*',function($view){
	if(Session::has('nickname'))
		$cfg['nickname']=Session::get('nickname');

	$cfg['ASP_NAME']='RSS Widget';
	$cfg['nowCalendar']='選択してください';
	$cfg['selected']='hide';
	if(Session::has('acc')){
		$cfg['acc']=Session::get('acc');
	}else{
		$cfg['acc']='アカウント名';
	}
	$cfg['HTMLselectsite'] = siteList1();

	$news = News::all();	// お知らせ用

	$view->with(compact('cfg','news'));
});

Route::get('test/remove',function(){
	$idAry = Article::groupBy(['blogid','url'])->havingRaw('count(*)>1')->lists('id');
	foreach($idAry as $id){
		echo "delete id : $id <br>";
		Article::where('id',$id)->delete();
	}
});

Route::get('test/ld',function(){

	// 	http://avmovie19.blog.jp
	$id = 'gm6wlw';
	$pwd = 'VM4IsGVPVv';
	$url= 'https://livedoor.blogcms.jp/atompub/gm6wlw/article';

	$title = 'テスト';
	$text = '<p>テスト投稿です。</p>';
	$cat = 'カテゴリ';
	$body =
	  '<?xml version="1.0"?>'.
	  '<entry xmlns="http://www.w3.org/2007/app" xmlns:atom="http://www.w3.org/2005/Atom">'.
	  '<title type="text/html" mode="escaped">'.$title.'</title>'.
	  '<content type="application/xhtml+xml">'.$text.'</content>'.
	  '<category term="'.$cat.'"/>'.
	  '</entry>';
	try{
	  $req = new HTTP_Request2();
	  $req->setUrl($url);
	  $req->setConfig(array('ssl_verify_host' => false,
	                        'ssl_verify_peer' => false
	                     ));
	  $req->setMethod(HTTP_Request2::METHOD_POST);
	  $req->setAuth($id, $pwd);
	  $req->setBody($body);
	  $req->send();
	} catch (HTTP_Request2_Exception $e) {
	  die($e->getMessage());
	} catch (Exception $e) {
	  die($e->getMessage());
	}

	return 'end';
});

Route::get('test/acc',function(){
	foreach(Sites::all() as $site):
		$blogs = Blogs::where('acc',$site->acc)->lists('id');
		// echo $site->acc.'<br>';
		// var_dump($blogs);
		// echo '<hr>';
		foreach($blogs as $blogid):
			Article::where('blogid',$blogid)->update(['acc'=>$site->acc]);
		endforeach;
	endforeach;
	// $step = 1000;
	// for($i=0;$i<10;$i++){
	// 	$article = Article::orderBy('created_at','desc')->take($step)->offset($i*$step)->get();
	// 	foreach($article as $item){
	// 		//$chk = Article::where('movSite',$item->movSite)->where('movid',$item->movid)->
	// 		if($blog = Blogs::where('id',$item->blogid)->first()){
	// 			printf("article_id:%d blog_id:%d site_acc:%s<br>",$item->id,$item->blogid,$item->blog->acc);
	// 			//Article::where('id',$item->id)->update(['acc'=>$item->blog->acc]);
	// 		}
	// 	}
	// 	printf("%d - %d<hr>",$i*$step,($i+1)*$step);
	// }
	return 'finish';
});

Route::get('test/dup',function(){
	//https://gyazo.com/26c8c7cf031cfa9188f6009edb47c2c8
	$movid=(\Input::has('movid'))?\Input::get('movid'):17378767;
	$movSite=(\Input::has('movSite'))?\Input::get('movSite'):'xvideo';

	$_chk = Article::where('movid',$movid)->where('movSite',$movSite);
	//var_dump($_chk->get());
	//var_dump($_chk);

	foreach($_chk->get() as $v){
		echo $v->blog->acc.'<br>';
	}
	return 1;

	//var_dump($_chk->blog->toSql());
	// foreach($_dupChk as $_dupChk_v){
	// 	if($_dupChk_v->blog->acc != $site->acc){
	// 		Article::where('id',$item_id)->update($input);
	// 		break;
	// 	}else{
	// 		//Article::where('id',$item_id)->delete();
	// 		echo "<div class='label label-danger'>この動画はすでに存在していますので削除しました</div><br>";
	// 		//break;
	// 	}
	// }
});

Route::get('test/getRss',function(){

	$gettime = time();

	$errorUrl = array();

	$blogs = Blogs::where('deleted_at','0000-00-00 00:00:00')->get();
	//dd($blogs);
	// rssurlからmulticurlで取得
	foreach($blogs as $blog){
		$url[$blog->id]=$blog->rssurl;
	}
	$rss = fetchMultiUrl($url,5,$errorUrl);

	var_dump($errorUrl);

	return (time()-$gettime).'sec';

});

Route::get('test/wp/deletePost/{siteid?}',function($siteid=3){
	$input = explode(',',Input::get('delId'));

	foreach($input as $id){
		$site = Sites::where('isActive',1)->where('postType',1)->where('id',$siteid)->first();

		include_once('./IXR_Library.php');
		$client = new IXR_Client($site->wphost);

		// カテゴリ取得
		$status = $client->query(
		  "wp.deletePost",
		  1,
		  $site->wpuser,
		  $site->wppass,
		  $id
		);

		if(!$status){
		  echo('error - '.$client->getErrorCode().' : '.$client->getErrorMessage().'<br>');
		}
		$result = $client->getResponse();
		echo "delete $id <br>";

		var_dump($result);
	}
	return 'finish';
});

Route::get('test/wp/getCategoryList/{id?}',function($id=73){
	// サイトの設定でpostType=1のサイトのもののみ記事精査
	if($id!=''){
		$site = Sites::where('isActive',1)->where('postType',1)->where('id',$id)->first();

		include_once('./IXR_Library.php');
		$client = new IXR_Client($site->wphost);

		// カテゴリ取得
		$status = $client->query(
		  "wp.getTerms",
		  1,
		  $site->wpuser,
		  $site->wppass,
		  'category'
		);

		if(!$status){
		  echo('error - '.$client->getErrorCode().' : '.$client->getErrorMessage().'<br>');
		}

		var_dump($client->getResponse());

		// foreach($client->getResponse() as $k=>$v){

		// 	switch($v['slug']){
		// 		case 'fc2':
		// 		case 'xhumster':
		// 		case 'xvideo':
		// 			$key = $v['slug'];
		// 			$data[$key]=$v['term_id'];
		// 			echo $v['term_id']." : $key <br>";
		// 			break;
		// 		default:
		// 			$key = $v['slug'];
		// 			$data[$key]=$v['term_id'];
		// 			echo $v['term_id']." : $key <br>";
		// 			break;
		// 	}

		// }

		//dd($client->getResponse());

		//array(4) { [0]=> array(9) { ["term_id"]=> string(3) "461" ["name"]=> string(9) "fc2動画" ["slug"]=> string(3) "fc2" ["term_group"]=> string(1) "0" ["term_taxonomy_id"]=> string(3) "461" ["taxonomy"]=> string(8) "category" ["description"]=> string(0) "" ["parent"]=> string(1) "0" ["count"]=> int(0) } [1]=> array(9) { ["term_id"]=> string(3) "462" ["name"]=> string(8) "Xhumster" ["slug"]=> string(8) "xhumster" ["term_group"]=> string(1) "0" ["term_taxonomy_id"]=> string(3) "462" ["taxonomy"]=> string(8) "category" ["description"]=> string(0) "" ["parent"]=> string(1) "0" ["count"]=> int(0) } [2]=> array(9) { ["term_id"]=> string(3) "460" ["name"]=> string(6) "xvideo" ["slug"]=> string(6) "xvideo" ["term_group"]=> string(1) "0" ["term_taxonomy_id"]=> string(3) "460" ["taxonomy"]=> string(8) "category" ["description"]=> string(0) "" ["parent"]=> string(1) "0" ["count"]=> int(0) } [3]=> array(9) { ["term_id"]=> string(1) "1" ["name"]=> string(18) "人妻熟女動画" ["slug"]=> string(54) "%e4%ba%ba%e5%a6%bb%e7%86%9f%e5%a5%b3%e5%8b%95%e7%94%bb" ["term_group"]=> string(1) "0" ["term_taxonomy_id"]=> string(1) "1" ["taxonomy"]=> string(8) "category" ["description"]=> string(0) "" ["parent"]=> string(1) "0" ["count"]=> int(665) } }

		if(0){
			$blogs = Blogs::where('acc',$site->acc)->get();

			foreach($blogs as $blog){
				$articles = Articles::where('blogid',$blog->id)->where('utime','>',date('Y-m-d H:i:s',strtotime('-1day')))->take(10)->get();
			}
		}
	}


});

Route::get('/', array('as'=>'top',function()
{
	$cookie = json_decode(Cookie::get('rsswidget'),true);

	return View::make('index',array('ASP_NAME'=>'RSS WIDGET','cookie'=>$cookie));
}));

Route::get('/login', function()
{
	return 'ログインエラー';
});

Route::post('/login', function()
{
	$isRemember = Input::get('remember');

	$cookie = array('email'=>Input::get('email'),'password'=>Input::get('password'),'remember'=>Input::get('remember'));
	$cookie = json_encode($cookie);

	if($isRemember){
		Cookie::queue('rsswidget', $cookie,60*60*24*7);
	}

	$res = DB::select("select * from users where email = ? AND password = ? and active = 1",array(Input::get('email'),Input::get('password')));
	if($res){
		Session::put('user', $res[0]->id);
		Session::put('nickname', $res[0]->nickname);
		return Redirect::intended('/rss/site');
	}else{
		return Redirect::back()->withInput();
	}
});

Route::get('/logout', array('as'=>'logout',function()
{
	Session::flush();
	return Redirect::to('/');
}));

Route::post('/regist',array('before'=>'csrf',function(){
	//POSTデータの受信
	$inputs=Input::except(array('_token'));
	//バリデーションルールの設定
	$rules=array(
		'email'=>'required',
		'password'=>'required|min:6|max:15',
		'password2'=>'required|min:6|max:15',
		'nickname'=>'required|max:30',
		'sex'=>'required',
		'birth'=>'required'
	);
	//バリデーション処理
	$val=Validator::make($inputs,$rules);
	//バリデーションNGなら
	if($val->fails()){
		return Redirect::back()
			->withErrors($val)
			->withInput();
	}
	//バリデーションOKなら
	User::create($inputs);
	return Redirect::to('/confirm');
}));

Route::get('/regist',array('as'=>'regist',function()
{
	// ここ作る
	return 'regist';
}));

Route::get('/regist/confirm',array('as'=>'confirm',function()
{
	// ここ作る
	return 'confirm';
}));

Route::get('/regist/fin',array('as'=>'fin'),function()
{
	return 'fin';
});

Route::get('/wppost',function()
{

	include_once('IXR_Library.php');

	$sites = Sites::all();

	foreach($sites as $siteValue){

		// サイト選択 & 記事選択
		// mode : selectXX
		// selid : selXX

		if($siteValue["wphost"]=='' || $siteValue['wpuser']=='' || $siteValue['wppass']=='') continue;
		if($siteValue['postType']==1) continue;

		$mode = $siteValue['select'.date('H')];
		$selectid = $siteValue['sel'.date('H')];
		echo "$mode | 0:IN高 1:返還率低 2:指定ID &raquo;";

		switch($mode){
			case 0:	// IN順
				$blogs = Blogs::where('acc',$siteValue['acc'])->orderby('in','desc')->first();
				break;
			case 1:	// 返還率低い順
				$blogs = Blogs::selectRaw('*,`out`/`in` as rate')->where('acc',$siteValue['acc'])->orderby('rate','asc')->first();
				break;
			case 2:	// ID指定
				$idAry = explode(',',$selectid);
				$blogs = Blogs::find($idAry[0]);
				break;
		}
		echo 'selected blog is '.$blogs['id'].'<br>';
		//var_dump(Session::all());
		//var_dump(DB::getQueryLog());
		//exit;

		$item = Article::where('blogid',$blogs['id'])->orderby('utime','desc')->first()->toArray();

		// 記事本文とseotitle,seodescを置換
		$siteValue['wptitle'] = str_replace('#title#',$item['title'],$siteValue['wptitle']);
		$siteValue['wptitle'] = str_replace('#imgurl#',$item['imgurl'],$siteValue['wptitle']);
		$siteValue['wptitle'] = str_replace('#url#',$item['url'],$siteValue['wptitle']);

		$siteValue['wpdesc'] = str_replace('#title#',$item['title'],$siteValue['wpdesc']);
		$siteValue['wpdesc'] = str_replace('#imgurl#',$item['imgurl'],$siteValue['wpdesc']);
		$siteValue['wpdesc'] = str_replace('#url#',$item['url'],$siteValue['wpdesc']);

		$siteValue['seotitle'] = str_replace('#title#',$item['title'],$siteValue['seotitle']);
		$siteValue['seotitle'] = str_replace('#imgurl#',$item['imgurl'],$siteValue['seotitle']);
		$siteValue['seotitle'] = str_replace('#url#',$item['url'],$siteValue['seotitle']);

		$siteValue['seodesc'] = str_replace('#title#',$item['title'],$siteValue['seodesc']);
		$siteValue['seodesc'] = str_replace('#imgurl#',$item['imgurl'],$siteValue['seodesc']);
		$siteValue['seodesc'] = str_replace('#url#',$item['url'],$siteValue['seodesc']);

		//echo $siteValue['wpdesc'];

		$client = new IXR_Client($siteValue['wphost']);

		$status = $client->query(
		  "wp.newPost", //使うAPIを指定（wp.newPostは、新規投稿）
		  1, // blog ID: 通常は１、マルチサイト時変更
		  $siteValue['wpuser'], // ユーザー名
		  $siteValue['wppass'], // パスワード
		  array(
		    //'post_author' => 1, // 投稿者ID 未設定の場合投稿者名なしになる。
		    'post_status' => 'publish', // 投稿状態
		    'post_title' => $siteValue['wptitle'], // タイトル
		    'post_content' => $siteValue['wpdesc'], //　本文
				'custom_fields' => array(
		      array('key' => '_aioseop_title', 'value' => $siteValue['seotitle']),
		      array('key' => '_aioseop_keyword', 'value' => $siteValue['seokeyword']),
		      array('key' => '_aioseop_description', 'value' => $siteValue['seodesc'])
				),
		    'terms' => array('category' => array(1))// カテゴリ追加
		  )
		);
		if(!$status){
		  die('error - '.$client->getErrorCode().' : '.$client->getErrorMessage());
		}

		$post_id = $client->getResponse(); //返り値は投稿ID

		echo "<a href='".$siteValue['wphost']."'>".$siteValue['name']."</a>( $post_id ) finish<hr>";
	}

	return "all done<hr>";

});
Route::get('mypage',['uses'=>'AdminController@mypage','as'=>'mypage']);
Route::post('mypage/edit',['uses'=>'AdminController@mypagePost']);

Route::get('rss',['uses'=>'AdminController@top','as'=>'top']);
Route::get('rss/site',['uses'=>'AdminController@site','as'=>'site']);
Route::get('rss/site/edit/{id?}',['uses'=>'AdminController@siteEdit','as'=>'site.edit']);
Route::post('rss/site/edit',['uses'=>'AdminController@sitePost','as'=>'site.update']);
Route::get('rss/site/del/{id?}',['uses'=>'AdminController@siteDel','as'=>'site.del']);

Route::get('rss/blog',['uses'=>'AdminController@blog','as'=>'blog']);
Route::get('rss/blog/edit/{id?}',['uses'=>'AdminController@blogEdit','as'=>'blog.edit']);
Route::post('rss/blog/edit',['uses'=>'AdminController@blogPost']);
Route::get('rss/blog/del/{id}',['uses'=>'AdminController@blogDel','as'=>'blog.del']);

Route::get('rss/parts',['uses'=>'AdminController@parts','as'=>'parts']);
Route::get('rss/parts/edit/{id?}',['uses'=>'AdminController@partsEdit','as'=>'parts.edit']);
Route::post('rss/parts/edit',['uses'=>'AdminController@partsPost']);
Route::get('rss/parts/del/{id}',['uses'=>'AdminController@partsDel','as'=>'parts.del']);

Route::get('rss/replace',['uses'=>'AdminController@replace','as'=>'replace']);
Route::get('rss/replace/edit/{id?}',['uses'=>'AdminController@replaceEdit','as'=>'replace.edit']);
Route::post('rss/replace/edit',['uses'=>'AdminController@replacePost']);
Route::get('rss/replace/del/{id}',['uses'=>'AdminController@replaceDel','as'=>'replace.del']);

Route::get('rss/article',['uses'=>'AdminController@article','as'=>'article']);
Route::get('rss/refloop',['uses'=>'AdminController@refloop','as'=>'refloop']);
Route::get('rss/refer/{id}',['uses'=>'AdminController@refer','as'=>'refer']);
Route::get('rss/referall/{id}',['uses'=>'AdminController@referall','as'=>'referall']);

Route::get('rss/ngword',['uses'=>'AdminController@ngword','as'=>'ngword']);
Route::get('rss/ngword/edit/{id?}',['uses'=>'AdminController@ngwordEdit','as'=>'ngword.edit']);
Route::post('rss/ngword/edit',['uses'=>'AdminController@ngwordPost']);
Route::get('rss/ngword/del/{id}',['uses'=>'AdminController@ngwordDel','as'=>'ngword.del']);

Route::get('rss/postword',['uses'=>'AdminController@postword','as'=>'postword']);
Route::get('rss/postword/edit/{id?}',['uses'=>'AdminController@postwordEdit','as'=>'postword.edit']);
Route::post('rss/postword/edit',['uses'=>'AdminController@postwordPost']);
Route::get('rss/postword/del/{id}',['uses'=>'AdminController@postwordDel','as'=>'postword.del']);

Route::get('rss/actress',['uses'=>'AdminController@actress','as'=>'actress']);
Route::get('rss/actress/edit/{id?}',['uses'=>'AdminController@actressEdit','as'=>'actress.edit']);
Route::post('rss/actress/edit',['uses'=>'AdminController@actressPost']);
Route::get('rss/actress/del/{id}',['uses'=>'AdminController@actressDel','as'=>'actress.del']);

Route::get('rss/noActress',['uses'=>'AdminController@noActress','as'=>'noActress']);
Route::get('rss/noActress/edit/{id?}',['uses'=>'AdminController@noActressEdit','as'=>'noActress.edit']);
Route::post('rss/noActress/edit',['uses'=>'AdminController@noActressPost']);
Route::get('rss/noActress/del/{id}',['uses'=>'AdminController@noActressDel','as'=>'noActress.del']);

/////////////////////////////////////////////////////////////////////
// サイト選択フォーム
/////////////////////////////////////////////////////////////////////

Route::post('/rss/site',array('before'=>'csrf',function(){
	Session::put('acc',Input::get('acc'));

	return Redirect::to('/rss/blog');
}));

Route::get('cron/rssGet/{id?}',['uses'=>'CronController@rssGet','as'=>'cron.rssGet']);
Route::get('cron/rssPost/{id?}',['uses'=>'CronController@rssPost','as'=>'cron.rssPost']);
Route::get('cron/cnt',['uses'=>'CronController@cnt','as'=>'cron.cnt']);
Route::get('cron/rssGet2',['uses'=>'CronController2@rssGet','as'=>'cron2.rssGet']);
Route::get('cron/rssPost2',['uses'=>'CronController2@rssPost','as'=>'cron2.rssGet']);

Route::get('/info',function()
{
	echo 'utime:'.time().'<br>';
	//phpinfo();
	return 'end';
});

