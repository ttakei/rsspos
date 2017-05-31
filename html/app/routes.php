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
	if(Session::has('nickname')) {
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
	}
});

Route::get('test/remove',function(){
	$idAry = Article2::groupBy(['blogid','url'])->havingRaw('count(*)>1')->lists('id');
	foreach($idAry as $id){
		echo "delete id : $id <br>";
		Article2::where('id',$id)->delete();
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
			Article2::where('blogid',$blogid)->update(['acc'=>$site->acc]);
		endforeach;
	endforeach;
	// $step = 1000;
	// for($i=0;$i<10;$i++){
	// 	$article = Article2::orderBy('created_at','desc')->take($step)->offset($i*$step)->get();
	// 	foreach($article as $item){
	// 		//$chk = Article2::where('movSite',$item->movSite)->where('movid',$item->movid)->
	// 		if($blog = Blogs::where('id',$item->blogid)->first()){
	// 			printf("article_id:%d blog_id:%d site_acc:%s<br>",$item->id,$item->blogid,$item->blog->acc);
	// 			//Article2::where('id',$item->id)->update(['acc'=>$item->blog->acc]);
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

	$_chk = Article2::where('movid',$movid)->where('movSite',$movSite);
	//var_dump($_chk->get());
	//var_dump($_chk);

	foreach($_chk->get() as $v){
		echo $v->blog->acc.'<br>';
	}
	return 1;

	//var_dump($_chk->blog->toSql());
	// foreach($_dupChk as $_dupChk_v){
	// 	if($_dupChk_v->blog->acc != $site->acc){
	// 		Article2::where('id',$item_id)->update($input);
	// 		break;
	// 	}else{
	// 		//Article2::where('id',$item_id)->delete();
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

/////////////////////////////////////////////////////////////////////
// トップ、ログイン
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
		Session::put('role',$res[0]->role);
		if ($res[0]->role == 'admin') {
			return Redirect::intended('/rss/site');
		} else {
			return Redirect::intended('/rss');
		}
	}else{
		return Redirect::back()->withInput();
	}
});
Route::get('/logout', array('as'=>'logout',function()
{
	Session::flush();
	return Redirect::to('/');
}));

/////////////////////////////////////////////////////////////////////
// ユーザー
Route::get('admin/user',['uses'=>'UserController@index','as'=>'admin.user.index']);
Route::get('admin/user/create',['uses'=>'UserController@create','as'=>'admin.user.create']);
Route::post('admin/user',['uses'=>'UserController@store','as'=>'admin.user.store']);
Route::get('admin/user/edit/{id}',['uses'=>'UserController@edit','as'=>'admin.user.edit']);
Route::post('admin/user/update',['uses'=>'UserController@update','as'=>'admin.user.update']);
Route::get('admin/user/delete/{id}',['uses'=>'UserController@delete','as'=>'admin.user.delete']);

/////////////////////////////////////////////////////////////////////
// マイページ
Route::get('mypage',['before'=>'myauth','uses'=>'AdminController@mypage','as'=>'mypage']);
Route::post('mypage/edit',['uses'=>'AdminController@mypagePost']);

/////////////////////////////////////////////////////////////////////
// サイト
Route::get('rss',['before'=>'myauth','uses'=>'AdminController@top','as'=>'top']);
Route::get('rss/site',['before'=>'admauth','uses'=>'AdminController@site','as'=>'site']);
Route::get('rss/site/edit/{id?}',['before'=>'admauth','uses'=>'AdminController@siteEdit','as'=>'site.edit']);
Route::post('rss/site/edit',['uses'=>'AdminController@sitePost','as'=>'site.update']);
Route::get('rss/site/del/{id?}',['before'=>'admauth','uses'=>'AdminController@siteDel','as'=>'site.del']);
Route::post('/rss/site',array('before'=>'csrf',function(){
	Session::put('acc',Input::get('acc'));
	if(Session::get('role')=='writer'){
		return Redirect::to('/rss/article');
	}else{
		return Redirect::to('/rss/blog');
	}
}));

/////////////////////////////////////////////////////////////////////
// ブログ
Route::get('rss/blog',['before'=>'admauth','uses'=>'AdminController@blog','as'=>'blog']);
Route::get('rss/blog/edit/{id?}',['before'=>'admauth','uses'=>'AdminController@blogEdit','as'=>'blog.edit']);
Route::post('rss/blog/edit',['uses'=>'AdminController@blogPost']);
Route::get('rss/blog/del/{id}',['before'=>'admauth','uses'=>'AdminController@blogDel','as'=>'blog.del']);

/////////////////////////////////////////////////////////////////////
// 記事
Route::get('rss/article',['before'=>'myauth','uses'=>'AdminController@article','as'=>'article']);
// TODO: AdminContollerに処理を移す
Route::get('/rss/article/edit/{id?}',array('before'=>'myauth','as'=>'article.edit',function($id=0)
{
	$cfg['nickname']=Session::get('nickname');
	$cfg['ASP_NAME']='RSS Widget';
	$cfg['selected']='';

	include_once('./IXR_Library.php');

	$site = Sites::where('acc',Session::get('acc'))->first();
	//dd($site);

	$client = new IXR_Client($site->wphost);

	//if(Config::get('app.debug')) dd($site);

	// カテゴリー取得
	// $status = $client->query(
	// 	"wp.getCategories",
	// 	1,
	// 	$site->wpuser,
	// 	$site->wppass
	// );
	// if(Config::get('app.debug')){
	// 	dd($client->getResponse());
	// }

	// タクソノミー取得(カテゴリー取得)
	$status = $client->query(
	  "wp.getTerms",
	  1,
	  $site->wpuser,
	  $site->wppass,
	  'category'
	);

	$categoryAry = [];
	if(!$status){
	  echo('error '.$client->getErrorCode().' : '.$client->getErrorMessage().'<br>');
	}else{
		//$categoryAry = $client->getResponse();
		foreach($client->getResponse() as $v){
			//var_dump($v);
			$key = $v['term_id'];
			$categoryAry[$key] = $v['name'];
		}
	}
	//if(Config::get('app.debug')) dd($categoryAry);

	// サイトセレクト
	$cfg['HTMLselectsite'] = siteList1();

	$articleObj = Article2::selectRaw('blogs.name as blogname,article2.*')
	->leftJoin('blogs','blogs.id','=','article2.blogid')
	->where('article2.id',$id)
	->first();

	//dd($articleObj);

	$show = 1;
	return View::make('articlesEdit',compact('cfg','articleObj','categoryAry','site', 'show'));

}));
// TODO: AdminContollerに処理を移す
Route::post('/rss/article/edit',function()
{
	//dd(Input::all());

	include_once('./IXR_Library.php');
	$site = Sites::where('acc',Session::get('acc'))->first();
	$client = new IXR_Client($site->wphost);
	$status = $client->query(
	  "wp.getTerms",
	  1,
	  $site->wpuser,
	  $site->wppass,
	  'category'
	);

	$categoryAry = [];
	if(!$status){
	  echo('error '.$client->getErrorCode().' : '.$client->getErrorMessage().'<br>');
	}else{
		//$categoryAry = $client->getResponse();
		foreach($client->getResponse() as $v){
			//var_dump($v);
			$key = $v['term_id'];
			$categoryAry[$key] = $v['name'];
		}
	}
	// var_dump($categoryAry);echo '<hr>';

	//dd(Input::all());
	$id = Input::get('id');

	$input = Input::except('_token','id','category','tag');

	if(Input::has('category')){
		$input['category'] = implode(',',Input::get('category'));
	}else{
		$input['category'] = '';
	}
	// var_dump(Input::get('category'));echo '<hr>';


	// 2016/11/06
	// タグ
	if(Input::has('tag')){
		$input['tag'] = implode(',',Input::get('tag'));
	}else{
		$input['tag'] = '';
	}
	// seo_keyword
	$_cat = '';
	if(Input::has('category') && is_array(Input::get('category'))){
		foreach(Input::get('category') as $k=>$v){
			$_cat .= $categoryAry[$v].',';
		}
		$_cat = substr($_cat,0,-1);
	}

	$input['seo_keyword'] = $_cat;
	$input['seo_keyword'] .= (Input::has('tag'))?','.$input['tag']:'';
	// seo_title
	$input['seo_title'] = $input['title_rewrite'];

	// newフラグを立てないと投稿されない
	$input['new'] = '1';
	
	//dd($input);

	// $rules=array(
	// 	'siteurl'=>'required|url',
	// 	'rssurl'=>'sometimes|url',
	// 	'name'=>'required|max:50'
	// );
	// //バリデーション処理
	// $val=Validator::make($input,$rules);
	// //バリデーションNGなら
	// if($val->fails()){
	// 	return Redirect::back()->withErrors($val)->withInput();
	// }

	Article2::where('id',$id)->update($input);

	return Redirect::to('/rss/article');
});
// TODO: AdminContollerに処理を移す
Route::get('/rss/article/del/{id}',array('before'=>'myauth',function($id)
{
	Article2::destroy($id);
	return Redirect::to('/rss/article');
}))->where('id','[0-9]+');
// TODO: AdminContollerに処理を移す
Route::get('/rss/check',['before'=>'myauth','as'=>'writer.check',function(){
	//$cfg['nickname']=Session::get('nickname');
	//$cfg['ASP_NAME']='RSS Widget';
	$cfg['selected']='';

	// サイトセレクト
	$cfg['HTMLselectsite'] = siteList1();

	// サイトに対するブログを選択
	$blogs = Blogs::where('acc',Session::get('acc'))->orderby('in','desc')->lists('id');

	// 予約投稿時刻が入力されている && 動画サービスありの記事を抽出
	$articles = Article2::selectRaw("blogs.name, blogs.siteurl,article2.*")
	->where('movSite','<>','')
	->where(function($query){
		$query->where('researved_at','<>','0000-00-00 00:00:00')
		->orWhere('posted_at', '<>','0000-00-00 00:00:00');
	})
	->leftJoin('blogs','blogs.id','=','article2.blogid')
	->where('blogs.acc',Session::get('acc'))
	->whereIn('article2.blogid',$blogs)
	->orderBy('updated_at','DESC')
	->paginate(30);

	$show = 1;
	return View::make('check.index',array('cfg'=>$cfg,'articles'=>$articles,'show'=>$show));
}]);


/////////////////////////////////////////////////////////////////////
// パーツ修正・追加（もう使わない）
Route::get('rss/parts',['uses'=>'AdminController@parts','as'=>'parts']);
Route::get('rss/parts/edit/{id?}',['uses'=>'AdminController@partsEdit','as'=>'parts.edit']);
Route::post('rss/parts/edit',['uses'=>'AdminController@partsPost']);
Route::get('rss/parts/del/{id}',['uses'=>'AdminController@partsDel','as'=>'parts.del']);

/////////////////////////////////////////////////////////////////////
// 置換設定
Route::get('rss/replace',['uses'=>'AdminController@replace','as'=>'replace']);
Route::get('rss/replace/edit/{id?}',['uses'=>'AdminController@replaceEdit','as'=>'replace.edit']);
Route::post('rss/replace/edit',['uses'=>'AdminController@replacePost']);
Route::get('rss/replace/del/{id}',['uses'=>'AdminController@replaceDel','as'=>'replace.del']);

/////////////////////////////////////////////////////////////////////
// リファラ設定（もう使わない）
Route::get('rss/refloop',['uses'=>'AdminController@refloop','as'=>'refloop']);
Route::get('rss/refer/{id}',['uses'=>'AdminController@refer','as'=>'refer']);
Route::get('rss/referall/{id}',['uses'=>'AdminController@referall','as'=>'referall']);

/////////////////////////////////////////////////////////////////////
// NGワード
Route::get('rss/ngword',['uses'=>'AdminController@ngword','as'=>'ngword']);
Route::get('rss/ngword/edit/{id?}',['uses'=>'AdminController@ngwordEdit','as'=>'ngword.edit']);
Route::post('rss/ngword/edit',['uses'=>'AdminController@ngwordPost']);
Route::get('rss/ngword/del/{id}',['uses'=>'AdminController@ngwordDel','as'=>'ngword.del']);
Route::get('rss/postword',['uses'=>'AdminController@postword','as'=>'postword']);
Route::get('rss/postword/edit/{id?}',['uses'=>'AdminController@postwordEdit','as'=>'postword.edit']);
Route::post('rss/postword/edit',['uses'=>'AdminController@postwordPost']);
Route::get('rss/postword/del/{id}',['uses'=>'AdminController@postwordDel','as'=>'postword.del']);

/////////////////////////////////////////////////////////////////////
// 女優名
Route::get('rss/actress',['uses'=>'AdminController@actress','as'=>'actress']);
Route::get('rss/actress/edit/{id?}',['uses'=>'AdminController@actressEdit','as'=>'actress.edit']);
Route::post('rss/actress/edit',['uses'=>'AdminController@actressPost']);
Route::get('rss/actress/del/{id}',['uses'=>'AdminController@actressDel','as'=>'actress.del']);
Route::get('rss/noActress',['uses'=>'AdminController@noActress','as'=>'noActress']);
Route::get('rss/noActress/edit/{id?}',['uses'=>'AdminController@noActressEdit','as'=>'noActress.edit']);
Route::post('rss/noActress/edit',['uses'=>'AdminController@noActressPost']);
Route::get('rss/noActress/del/{id}',['uses'=>'AdminController@noActressDel','as'=>'noActress.del']);

/////////////////////////////////////////////////////////////////////
// RSS取得、サイト投稿
Route::get('cron/cnt',['uses'=>'CronController@cnt','as'=>'cron.cnt']);
Route::get('cron/rssGet2',['uses'=>'CronController2@rssGet','as'=>'cron.rssGet2']);
Route::get('cron/rssPost2',['uses'=>'CronController2@rssPost','as'=>'cron.rssPost2']);
