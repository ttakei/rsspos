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

View::composer(['*'],function($view){
	if(Session::has('nickname')){
		$_cfg['nickname']=Session::get('nickname');
		$_cfg['ASP_NAME']='RSS Widget';
		$_cfg['HTMLselectsite'] = siteList1();
		if(Session::has('acc')){
			$db_acc = Sites::where('isActive',1)->where('acc',Session::get('acc'));
        	if (empty($db_acc) || !($db_acc->first())) {
				$Gsite = [];
			} else {
				$db_acc->first()->toArray();
			}
		}else{
			$Gsite = [];
		}
		$view->with(compact('_cfg','Gsite'));
	}
});

Route::get('admin/user',['uses'=>'UserController@index','as'=>'admin.user.index']);
Route::get('admin/user/create',['uses'=>'UserController@create','as'=>'admin.user.create']);
Route::post('admin/user',['uses'=>'UserController@store','as'=>'admin.user.store']);
Route::get('admin/user/edit/{id}',['uses'=>'UserController@edit','as'=>'admin.user.edit']);
Route::post('admin/user/update',['uses'=>'UserController@update','as'=>'admin.user.update']);
Route::get('admin/user/delete/{id}',['uses'=>'UserController@delete','as'=>'admin.user.delete']);

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

Route::get('test/wp/getCategoryList',function($id=73){
	// サイトの設定でpostType=1のサイトのもののみ記事精査
	if(Input::has('id')){
		$sites = Sites::where('isActive',1)->where('postType',1)->where('id',$id)->first();
	}else{
		$sites = Sites::all();
	}

	include_once('./IXR_Library.php');

	foreach($sites as $site){
		$client = new IXR_Client($site->wphost);

		// カテゴリー取得
		$status = $client->query(
			"wp.getCategories",
			1,
			$site->wpuser,
			$site->wppass
		);
		var_dump($client->getResponse());
		echo '<HR>';

		// タクソノミー取得(カテゴリー取得)
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

		foreach($client->getResponse() as $k=>$v){

			// urlencodeされている！！
			//echo urldecode($v['slug']).'<hr>';
			switch($v['slug']){
				case 'fc2':
				case 'xhumster':
				case 'xvideo':
					$key = $v['slug'];
					$data[$key]=$v['term_id'];
					echo $v['term_id']." : $key <br>";
					break;
				default:
					$key = $v['slug'];
					$data[$key]=$v['term_id'];
					echo $v['term_id']." : $key <br>";
					echo ($key);
					break;
			}

		}

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
		Session::put('role',$res[0]->role);
		return Redirect::intended('/rss');
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

/////////////////////////////////////////////////////////////////////
// マイページ編集
/////////////////////////////////////////////////////////////////////

Route::post('/mypage/edit', array(function()
{
	$id = Input::get('id');

	$input = Input::except('_token','id');

	// nicknameが変わる可能性あるので
	Session::put('nickname',Input::get('nickname'));

	if(isset($id) && $id!=0){
		Users::where('id',$id)->update($input);
	}

	return Redirect::to('/mypage');
}));

/////////////////////////////////////////////////////////////////////
// マイページ
/////////////////////////////////////////////////////////////////////


Route::get('/mypage', array('before'=>'myauth','as'=>'mypage',function()
{
	$cfg['nickname']=Session::get('nickname');
	$cfg['ASP_NAME']='RSS Widget';
	$cfg['nowCalendar']='選択してください';
	$cfg['selected']='hide';
	if(Session::has('acc')){
		$cfg['acc']=Session::get('acc');
	}else{
		$cfg['acc']='アカウント名';
	}
	//phpinfo();
	//dd(Session::all());

	$news = News::all();	// お知らせ用
	$sites = Sites::all();	// サイトリスト用

	// サイトセレクト
	$cfg['HTMLselectsite'] = siteList1();

	$user = json_decode(Users::find(Session::get('user')),true);

	return View::make('mypage',array('news'=>$news,'cfg'=>$cfg,'sites'=>$sites,'user'=>$user));

}));





/////////////////////////////////////////////////////////////////////
// rssTOP画面
/////////////////////////////////////////////////////////////////////

Route::get('/rss', array('before'=>'myauth','as'=>'rssselect',function()
{
	//var_dump(Session::all());
	$cfg['nickname']=Session::get('nickname');
	$cfg['ASP_NAME']='RSS Widget';
	$cfg['nowCalendar']='選択してください';
	$cfg['selected']='hide';

	$news = News::all();	// お知らせ用
	$sites = Sites::all();	// サイトリスト用

	// サイトセレクト
	$cfg['HTMLselectsite'] = siteList1();

	return View::make('rss',array('news'=>$news,'cfg'=>$cfg,'sites'=>$sites));
}));

/////////////////////////////////////////////////////////////////////
// サイト選択フォーム
/////////////////////////////////////////////////////////////////////

Route::post('/rss/site',array('before'=>'csrf',function(){
	Session::put('acc',Input::get('acc'));

	if(Session::get('role')=='writer'){
		return Redirect::to('/rss/article');
	}else{
		return Redirect::to('/rss/blog');
	}
}));


Route::get('/rss/site',array('before'=>'admauth','as'=>'sitelist',function()
{
	//dd(Session::all());
	$cfg['nickname']=Session::get('nickname');
	$cfg['ASP_NAME']='RSS Widget';
	$cfg['selected']='';

	$sites = Sites::where('userid',Session::get('user'))->get();

	// サイトセレクト
	$cfg['HTMLselectsite'] = siteList1();

	return View::make('site',array('cfg'=>$cfg,'sites'=>$sites));

}));

Route::get('/rss/site/edit/{id?}',array('before'=>'admauth','as'=>'siteedit',function($id=0)
{
	$cfg['nickname']=Session::get('nickname');
	$cfg['ASP_NAME']='RSS Widget';
	$cfg['selected']='';

	$news = News::all();	// お知らせ用

	// サイトセレクト
	$cfg['HTMLselectsite'] = siteList1();

	if($id!=0){
		$sites = Sites::where('id',$id)->first();
	}else{

		$title = new Sites;
		$query = 'SHOW COLUMNS FROM ' . $title->getTable();
		foreach (DB::select($query) as $column) {
			switch($column->Field){
				case 'created_at':
				case 'updated_at':
				case 'deleted_at':
					break;
				case 'isactive':
				  $sites[$column->Field] = 1;
				  break;
				 case 'postType':
				 	$sites[$column->Field] = 1;
				 	break;
				default:
				  $sites[$column->Field] = '';
				  break;
			}
		}
	}
	//$shifts = Shifts::where('shift_sid',$id)->orderby('shift_hh','ASC')->get();
	//dd($shifts);
	$itemSelect = array('0'=>'IN順','1'=>'OUT率','2'=>'指定ブログ');
	return View::make('siteedit',array('cfg'=>$cfg,'news'=>$news,'site'=>$sites,'itemSelect'=>$itemSelect));
	//return View::make('siteedit',array('cfg'=>$cfg,'news'=>$news,'site'=>$sites,'itemSelect'=>$itemSelect,'shifts'=>$shifts));

}))->where('id','[0-9]+');

Route::post('/rss/site/edit',function()
{
	//dd(Session::all());
	//dd(Input::all());
	$id = Input::get('id');

	$input = Input::except('_token','id');

	$rules=array(
		'acc'=>'required|max:15|alpha_num',
		'isactive'=>'required',
		'name'=>'required|max:40'
	);
	//バリデーション処理
	$val=Validator::make($input,$rules);
	if($val->fails()){
		return Redirect::back()->withErrors($val)->withInput();
	}

	if(isset($id) && $id!=0){
		Sites::where('id',$id)->update($input);
	}else{
		$input['userid']=Session::get('user');
		$site = Sites::create($input);
		UserSites::create(['users_id'=>1,'site_id'=>$site->id]);
	}
	return Redirect::to('/rss/site');
});

Route::get('/rss/site/del/{id}',array('before'=>'admauth',function($id)
{
	Sites::destroy($id);
	UserSites::where('site_id',$id)->delete();
	return Redirect::to('/rss/site');
}))->where('id','[0-9]+');

/////////////////////////////////////////////////////////////////////
// ブログリスト
/////////////////////////////////////////////////////////////////////

Route::get('/rss/blog',array('before'=>'admauth','as'=>'bloglist',function()
{
	$cfg['nickname']=Session::get('nickname');
	$cfg['ASP_NAME']='RSS Widget';
	$cfg['selected']='';

	// $s1 = sprintf("SELECT url,count(*) as cnt FROM rsswidget.inalllog WHERE acc = '%s' GROUP BY url ORDER BY cnt DESC",$id);
	//$anly = DB::select("SELECT url,count(*) as cnt FROM inalllog WHERE acc = ? GROUP BY url ORDER BY cnt DESC",[Session::get('acc')]);
	//var_dump($anl);
	$blogs = Blogs::where('acc',Session::get('acc'))->orderby('in','desc')->get();

	$cnt['in'] = Blogs::where('acc',Session::get('acc'))->sum('in');
	$cnt['out'] = Blogs::where('acc',Session::get('acc'))->sum('out');
	//$pv = DB::select("SELECT count(*) as cnt FROM inalllog WHERE acc=?",[Session::get('acc')]);
	//foreach($pv as $v){ $cnt['pv']=$v->cnt;}

	//dd(Session::get('acc'));

	// サイトセレクト
	$cfg['HTMLselectsite'] = siteList1();

	return View::make('blog',array('cfg'=>$cfg,'blogs'=>$blogs,'cnt'=>$cnt));

}));

Route::get('/rss/refloop',array('before'=>'admauth','as'=>'refloop',function()
{
	$cfg['nickname']=Session::get('nickname');
	$cfg['ASP_NAME']='RSS Widget';
	$cfg['selected']='';

	// $s1 = sprintf("SELECT url,count(*) as cnt FROM rsswidget.inalllog WHERE acc = '%s' GROUP BY url ORDER BY cnt DESC",$id);
	$anly = DB::select("SELECT url,count(*) as cnt FROM inalllog WHERE acc = ? GROUP BY url ORDER BY cnt DESC",[Session::get('acc')]);
	//var_dump($anl);
	$blogs = Blogs::where('acc',Session::get('acc'))->orderby('in','desc')->get();

	$cnt['in'] = Blogs::where('acc',Session::get('acc'))->sum('in');
	$cnt['out'] = Blogs::where('acc',Session::get('acc'))->sum('out');
	$pv = DB::select("SELECT count(*) as cnt FROM inalllog WHERE acc=?",[Session::get('acc')]);
	foreach($pv as $v){ $cnt['pv']=$v->cnt;}

	//dd(Session::get('acc'));

	// サイトセレクト
	$cfg['HTMLselectsite'] = siteList1();

	return View::make('refloop',array('cfg'=>$cfg,'blogs'=>$blogs,'anly'=>$anly,'cnt'=>$cnt));

}));

// これがあると干渉する
/*Route::get('/rss/blog/{id?}',function($id)
{
	Session::put('acc',$id);
	return Redirect::to('/rss/blog');
});*/

/////////////////////////////////////////////////////////////////////
// ブログ編集・追加
/////////////////////////////////////////////////////////////////////

Route::get('/rss/blog/edit/{id?}',array('before'=>'admauth','as'=>'blogedit',function($id=0)
{
	$cfg['nickname']=Session::get('nickname');
	$cfg['ASP_NAME']='RSS Widget';
	$cfg['selected']='';

	// サイトセレクト
	$cfg['HTMLselectsite'] = siteList1();

	if($id!=0){
		$blogs = Blogs::where('acc',Session::get('acc'))->where('id',$id)->get();
		return View::make('blogedit',array('cfg'=>$cfg,'blogs'=>$blogs[0]));
	}else{
		if($id!=''){
			$url = "http://".$id."/";
		}else{
			$url = '';
		}
		$blogs = array('id'=>'','name'=>'','siteurl'=>$url,'rssurl'=>'','image'=>'','refer'=>'','title'=>'','url'=>'','domTitle'=>'','domBody'=>'','filterBody1'=>'','filterBody2'=>'');
		return View::make('blogedit',array('cfg'=>$cfg,'blogs'=>$blogs));
	}

}));

Route::post('/rss/blog/edit',function()
{
	$id = Input::get('id');

	$input = Input::except('_token','id');

	$rules=array(
		'siteurl'=>'required|url',
		'rssurl'=>'sometimes|url',
		'name'=>'required|max:50'
	);
	//バリデーション処理
	$val=Validator::make($input,$rules);
	//バリデーションNGなら
	if($val->fails()){
		return Redirect::back()->withErrors($val)->withInput();
	}

	if(isset($id) && $id!=''){
		Blogs::where('id',$id)
		->where('acc',Session::get('acc'))
		->update($input);
	}else{
		$input['acc']=Session::get('acc');
		Blogs::create($input);
	}
	return Redirect::to('/rss/blog');
});

/////////////////////////////////////////////////////////////////////
// ブログ削除
/////////////////////////////////////////////////////////////////////

Route::get('/rss/blog/del/{id}',array('before'=>'admauth',function($id)
{
	Blogs::destroy($id);
	return Redirect::to('/rss/blog');
}))->where('id','[0-9]+');



/////////////////////////////////////////////////////////////////////
// 記事リスト
/////////////////////////////////////////////////////////////////////

Route::get('/rss/article', array('before'=>'myauth','as'=>'articlelist',function()
{
	$cfg['nickname']=Session::get('nickname');
	$cfg['ASP_NAME']='RSS Widget';
	$cfg['selected']='';

	// サイトセレクト
	$cfg['HTMLselectsite'] = siteList1();

	// サイトに対するブログを選択
	$blogs = Blogs::where('acc',Session::get('acc'))->orderby('in','desc')->lists('id');

	$articles = Article::selectRaw("blogs.name, blogs.siteurl,article.*")
	->where('movSite','<>','')
	->leftJoin('blogs','blogs.id','=','article.blogid')
	->where('blogs.acc',Session::get('acc'))
	->whereIn('article.blogid',$blogs)
	->orderBy('created_at','DESC')
	->paginate(30);

	return View::make('articles',array('cfg'=>$cfg,'articles'=>$articles));
}));

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

	$articleObj = Article::selectRaw('blogs.name as blogname,article.*')
	->leftJoin('blogs','blogs.id','=','article.blogid')
	->where('article.id',$id)
	->first();

	//dd($articleObj);

	return View::make('articlesEdit',compact('cfg','articleObj','categoryAry','site'));

}));

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
	if(Input::has('category')){
		foreach(Input::get('category') as $k=>$v){
			$_cat .= $categoryAry[$v].',';
		}
		$_cat = substr($_cat,0,-1);
	}

	$input['seo_keyword'] = $_cat;
	$input['seo_keyword'] .= (Input::has('tag'))?','.$input['tag']:'';
	// seo_title
	$input['seo_title'] = $input['title_rewrite'];

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

	Article::where('id',$id)->update($input);

	return Redirect::to('/rss/article');
});

Route::get('/rss/article/del/{id}',array('before'=>'myauth',function($id)
{
	Article::destroy($id);
	return Redirect::to('/rss/article');
}))->where('id','[0-9]+');


Route::get('/rss/check',['before'=>'admauth','as'=>'writer.check',function(){
	//$cfg['nickname']=Session::get('nickname');
	//$cfg['ASP_NAME']='RSS Widget';
	$cfg['selected']='';

	// サイトセレクト
	$cfg['HTMLselectsite'] = siteList1();

	// サイトに対するブログを選択
	$blogs = Blogs::where('acc',Session::get('acc'))->orderby('in','desc')->lists('id');

	// 予約投稿時刻が入力されている && 動画サービスありの記事を抽出
	$articles = Article::selectRaw("blogs.name, blogs.siteurl,article.*")
	->where('movSite','<>','')
	->where('researved_at','<>','0000-00-00 00:00:00')
	->leftJoin('blogs','blogs.id','=','article.blogid')
	->where('blogs.acc',Session::get('acc'))
	->whereIn('article.blogid',$blogs)
	->orderBy('updated_at','DESC')
	->paginate(30);

	return View::make('check.index',array('cfg'=>$cfg,'articles'=>$articles));
}]);

/////////////////////////////////////////////////////////////////////
// パーツリスト
/////////////////////////////////////////////////////////////////////

Route::get('/rss/parts', array('before'=>'admauth','as'=>'partslist',function()
{
	$cfg['nickname']=Session::get('nickname');
	$cfg['ASP_NAME']='RSS Widget';
	$cfg['selected']='';

	// サイトセレクト
	$cfg['HTMLselectsite'] = siteList1();

	$parts = Parts::where('acc',Session::get('acc'))->get();

	return View::make('parts',array('cfg'=>$cfg,'parts'=>$parts));
}));

/////////////////////////////////////////////////////////////////////
// パーツ削除
/////////////////////////////////////////////////////////////////////

Route::get('/rss/parts/del/{id}',array('before'=>'admauth',function($id)
{
	Parts::destroy($id);
	return Redirect::to('/rss/parts');
}))->where('id','[0-9]+');

/////////////////////////////////////////////////////////////////////
// パーツ修正・追加
/////////////////////////////////////////////////////////////////////

Route::get('/rss/parts/edit/{id?}',array('before'=>'admauth','as'=>'partsedit',function($id=0)
{
	$cfg['nickname']=Session::get('nickname');
	$cfg['ASP_NAME']='RSS Widget';
	$cfg['selected']='';

	// サイトセレクト
	$cfg['HTMLselectsite'] = siteList1();

	if($id!=0){
		$parts = Parts::where('acc',Session::get('acc'))->where('id',$id)->get();
		return View::make('partsedit',array('cfg'=>$cfg,'parts'=>$parts[0]));
	}else{
		$parts = array('id'=>'','name'=>'','tpl'=>'','adline1'=>'','adtpl1'=>'','adline2'=>'','adtpl2'=>'','adline3'=>'','adtpl3'=>'');
		return View::make('partsedit',array('cfg'=>$cfg,'parts'=>$parts));
	}
}))->where('id','[0-9]+');

Route::post('/rss/parts/edit',function()
{
	$id = Input::get('id');

	$input = Input::except('_token','id');

	if(isset($id) && $id!=''){
		Parts::where('id',$id)
		->where('acc',Session::get('acc'))
		->update($input);
	}else{

		$input['acc']=Session::get('acc');
		Parts::create($input);

	}
	apc_delete("rsswdg_parts_".$id);
	return Redirect::to('/rss/parts');
});


Route::get('/info',function()
{
	echo 'utime:'.time().'<br>';
	//phpinfo();
	return 'end';
});



	///////////////////////////////////////////////////////////////////////
	// 置換設定

Route::get('/replace',function()
{
	$cfg['nickname']=Session::get('nickname');
	$cfg['ASP_NAME']='RSS Widget';
	$cfg['selected']='';

	// サイトセレクト
	$cfg['HTMLselectsite'] = siteList1();

	$words = ReplaceWords::all();
	return View::make('replace',compact('words','cfg'));
});

Route::get('/replace/edit/{id?}',function($id=0)
{

	$cfg['nickname']=Session::get('nickname');
	$cfg['ASP_NAME']='RSS Widget';
	$cfg['selected']='';

	// サイトセレクト
	$cfg['HTMLselectsite'] = siteList1();

	if($id!=0){
		$word = ReplaceWords::where('id',$id)->first();
	}else{
		$word = array('id'=>'','from'=>'','to'=>'');
	}
	return View::make('replaceedit',compact('word','cfg'));
});

Route::post('/replace/edit',function()
{
	$id = Input::get('id');

	$input = Input::except('_token','id');

	$rules=array(
		'from'=>'required',
		'to'=>'required'
	);
	//バリデーション処理
	$val=Validator::make($input,$rules);
	if($val->fails()){
		return Redirect::back()->withErrors($val)->withInput();
	}

	if(isset($id) && $id!=0){
		ReplaceWords::where('id',$id)->update($input);
	}else{
		ReplaceWords::create($input);
	}

	return Redirect::to('/replace');
});

Route::get('/replace/delete/{id}',function($id=0)
{
	ReplaceWords::destroy($id);
	return Redirect::to('/replace');
});

Route::get('/rss/refer/{id}',array('before'=>'admauth','as'=>'referlist',function($id='')
{
	$cfg['nickname']=Session::get('nickname');
	$cfg['ASP_NAME']='RSS Widget';
	$cfg['selected']='';
	$cfg['id'] = $id;

	//$anly = Inalllog::where('acc',Session::get('acc'))->where('url',"http://".$id."/")->paginate(50);
	//$anly = DB::select("SELECT * FROM inalllog WHERE acc = ? AND url = ?",[Session::get('acc'),"http://".$id."/"]);

	///$anly = DB::table('inalllog')->where('acc','=',Session::get('acc'))->where('url','=',"http://".$id."/")->orderby('utime','desc')->paginate(50);

	// refer毎の集計を表示したいらしい
	//$s1 = sprintf("SELECT url,count(*) as cnt FROM rsswidget.inalllog WHERE acc = '%s' GROUP BY url ORDER BY cnt DESC",$id);
	$anly = DB::table('inalllog')->select(DB::raw('refer,count(*) as cnt'))->where('acc','=',Session::get('acc'))->where('url','=',"http://".$id."/")->groupby('refer')->orderby('cnt','desc')->paginate(50);

	// サイトセレクト
	$cfg['HTMLselectsite'] = siteList1();

	return View::make('refer',array('cfg'=>$cfg,'refer'=>$anly));

}));

Route::get('/rss/referall/{id}',array('before'=>'admauth','as'=>'referalllist',function($id='')
{
	$cfg['nickname']=Session::get('nickname');
	$cfg['ASP_NAME']='RSS Widget';
	$cfg['selected']='';
	$cfg['id'] = $id;

	$anly = DB::table('inalllog')->where('acc','=',Session::get('acc'))->where('url','=',"http://".$id."/")->orderby('utime','desc')->paginate(50);

	// サイトセレクト
	$cfg['HTMLselectsite'] = siteList1();

	return View::make('referall',array('cfg'=>$cfg,'refer'=>$anly));

}));
