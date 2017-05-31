<?php

class AdminController extends BaseController {

	public function __construct(){
		//$this->beforeFilter('csrf', array('on' => 'post'));
		$this->beforeFilter('myauth', ['except'=>'']);
	}

	public function mypage(){
		$cfg['selected']='hide';
		$news = News::all(); // お知らせ用
		$sites = Sites::all()->sortBy("id"); // サイトリスト用
		$user = json_decode(Users::find(Session::get('user')),true);
		return View::make('mypage',compact('cfg','news','sites','user'));
	}

	public function mypagePost(){
		$id = Input::get('id');
		$input = Input::except('_token','id');

		// nicknameが変わる可能性あるので
		Session::put('nickname',Input::get('nickname'));

		if(isset($id) && $id!=0){
			Users::where('id',$id)->update($input);
		}

		return Redirect::route('mypage');
	}

	public function top(){
		$sites = Sites::all()->sortBy("id"); // サイトリスト用
		return View::make('rss',compact('sites'));
	}

	public function site(){
		$show=1;
		$userSites = Users::find(Session::get('user'))->sites->lists('site_id');
		$sites = Sites::whereIn('id',$userSites);
		
		//$sites = Sites::where('userid',Session::get('user'));
		if(Input::has('sitetype') && Input::get('sitetype')!='*')
			$sites = $sites->where('sitetype',Input::get('sitetype'));
		if(Input::has('category') && Input::get('category')!='*')
			$sites = $sites->where('category',Input::get('category'));
		$sites = $sites->orderBy('id', 'asc')->get();

		return View::make('site',compact('show','sites'));
	}

	public function siteEdit($id=0){
		$cfg['selected']='';

		if($id!=0){
			$sites = Sites::where('id',$id)->first();
		}else{
			$sites = [];
			$sites['post_status'] = 'publish';

			$title = new Sites;
			$query = 'SHOW COLUMNS FROM ' . $title->getTable();
			foreach (DB::select($query) as $column) {
				switch($column->Field){
					case 'created_at':
					case 'updated_at':
					case 'deleted_at':
						break;
					case 'post_status':
						$sites[$column->Field] = 'publish';
						break;
					case 'isEyecatch':
					case 'isNeedmov':
					case 'isNeedimg':
					case 'isactive':
					case 'useReplaceWords':
					  $sites[$column->Field] = 1;
					  break;
					case 'xvideo':
					case 'fc2':
					case 'fc2ja':
					case 'xhamster':
					case 'redtube':
					case 'erovideonet':
					case 'pornhub':
					case 'pipii':
					case 'javynow':
					case 'VJAV':
					case 'ShareVideos':
					case 'TokyoTube':
					case 'Tube8':
					case 'spankbang':
					case 'youporn':
					case 'txxx':
					  $sites[$column->Field] = array_get(Config::get('app.defTpl'),$column->Field);
						break;
					case 'xvideo__movlink':
					case 'fc2__movlink':
					case 'fc2ja__movlink':
					case 'xhamster__movlink':
					case 'redtube__movlink':
					case 'erovideonet__movlink':
					case 'pornhub__movlink':
					case 'pipii__movlink':
					case 'javynow__movlink':
					case 'VJAV__movlink':
					case 'ShareVideos__movlink':
					case 'TokyoTube__movlink':
					case 'Tube8__movlink':
					case 'spankbang__movlink':
					case 'youporn__movlink':
					case 'txxx__movlink':
					  $sites[$column->Field] = array_get(Config::get('app.defLinkTpl'),$column->Field);
						break;
					case 'wptitle':
					  $sites[$column->Field] = '#title#';
						break;
					case 'wpdesc':
					  $sites[$column->Field] = '<a href="#url#">
#title#
<img src="#imgurl#">
 #content#
</a>';
						break;
					default:
					  $sites[$column->Field] = '';
					  break;
				}
			}
			//dd($sites['post_status']);
		}
		$itemSelect = array('0'=>'IN順','1'=>'OUT率','2'=>'指定ブログ');
		return View::make('siteedit',array('cfg'=>$cfg,'site'=>$sites,'itemSelect'=>$itemSelect));
	}

	public function sitePost(){
		$id = Input::get('id');

		$input = Input::except('_token','id');

		$rules=array(
			'url'=>'required',
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
			return Redirect::route('site');
		}else{
			$input['userid']=Session::get('user');
			Sites::create($input);
			Session::put('acc',$input['acc']);
			return Redirect::to('/rss/blog');
		}
	}

	public function siteDel($id){
		Sites::destroy($id);
		return Redirect::route('site');
	}

	public function blog(){
		$show = 1;
		$blogs = Blogs::where('acc',Session::get('acc'))->orderby('in','desc')->get();
		$cnt['in'] = Blogs::where('acc',Session::get('acc'))->sum('in');
		$cnt['out'] = Blogs::where('acc',Session::get('acc'))->sum('out');

		return View::make('blog',array('show'=>$show,'blogs'=>$blogs,'cnt'=>$cnt));
	}

	public function blogEdit($id=0){
		$show = 1;

		if($id!=0){
			$blogs = Blogs::where('acc',Session::get('acc'))->where('id',$id)->first();
		}else{
			if($id!=''){
				$url = "http://".$id."/";
			}else{
				$url = '';
			}
			$blogs = array('id'=>'','name'=>'','siteurl'=>$url,'rssurl'=>'','image'=>'','refer'=>'','title'=>'','url'=>'','domTitle'=>'','domBody'=>'','filterBody1'=>'','filterBody2'=>'');
		}
		return View::make('blogedit',array('show'=>$show,'blogs'=>$blogs));
	}

	public function blogPost(){
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
		return Redirect::route('blog');
	}

	public function blogDel($id){
		Blogs::destroy($id);
		return Redirect::route('blog');
	}

	public function article(){
		$show = 1;

		// サイトに対するブログを選択
		$site = Sites::where('acc',Session::get('acc'))->first();		
		$blogs = Blogs::where('acc',Session::get('acc'))->orderby('in','desc')->lists('id');
		
		$articles = Article2::selectRaw("article2.id, article2.imgurl, article2.url, article2.title, article2.title_org, article2.created_at, article2.researved_at, article2.posted_at, blogs.name, blogs.siteurl,article2.movSite,article2.movid,article2.movlink")
			->whereIn('article2.blogid',$blogs);
		// 画像必須の場合
		if ($site->isNeedimg) {
			$articles = $articles->where('imgurl','<>','');
		}
		// 動画必須の場合
		if ($site->isNeedmov) {
			// 設定しているテンプレート
			$use_mov_tmpl = array();
			foreach (Config::get('app.movService') as $col => $ms) {
				if ($site->$col) {
					//$use_mov_tmpl[$ms] = true;
					$use_mov_tmpl[] = $ms;
				}
			}
			$use_movlink_tmpl = array();
			foreach (Config::get('app.movLinkService') as $col => $ms) {
				if ($site->$col) {
					//$use_movlink_tmpl[$ms] = true;
					$use_movlink_tmpl[] = $ms;
				}
			}
			
			$articles = $articles->where('movSite', '<>', '')
			->where(function($query) use($use_mov_tmpl, $use_movlink_tmpl) {
				$query->where(function($query2) use ($use_mov_tmpl) {
					$query2->where('movid', '<>', '')
					->whereIn('movSite', $use_mov_tmpl);
				})
				->orwhere(function($query3) use ($use_movlink_tmpl) {
					$query3->where('movlink', '<>' ,'')
					->whereIn('movSite', $use_movlink_tmpl);
				});
			});
		}
		$articles = $articles->orderBy('created_at','DESC')
			->leftJoin('blogs','blogs.id','=','article2.blogid')
			->where('blogs.acc',Session::get('acc'))
			->paginate(30);

		return View::make('articles',array('show'=>$show,'site'=>$site,'articles'=>$articles));
	}

	public function parts(){
		$show = 1;
		$parts = Parts::where('acc',Session::get('acc'))->get();
		return View::make('parts',array('show'=>$show,'parts'=>$parts));
	}

	public function partsEdit($id=0){
		$show = 1;
		if($id!=0){
			$parts = Parts::where('acc',Session::get('acc'))->where('id',$id)->first();
		}else{
			$parts = array('id'=>'','name'=>'','tpl'=>'','adline1'=>'','adtpl1'=>'','adline2'=>'','adtpl2'=>'','adline3'=>'','adtpl3'=>'');
		}
		return View::make('partsedit',array('show'=>$show,'parts'=>$parts));
	}

	public function partsPost(){
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
		return Redirect::route('parts');
	}

	public function partsDel($id){
		Parts::destroy($id);
		return Redirect::route('parts');
	}

	public function replace(){
		$show = 1;
		$words = ReplaceWords::all();
		return View::make('replace',compact('words','show'));
	}

	public function replaceEdit($id=0){
		$show = 1;
		if($id!=0){
			$word = ReplaceWords::where('id',$id)->first();
		}else{
			$word = array('id'=>'','from'=>'','to'=>'');
		}
		return View::make('replaceedit',compact('word','show'));
	}

	public function replacePost(){
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

		return Redirect::route('replace');
	}

	public function replaceDel($id){
		ReplaceWords::destroy($id);
		return Redirect::route('replace');
	}

	public function refloop(){
		$show = 1;

		$anly = DB::select("SELECT url,count(*) as cnt FROM inalllog WHERE acc = ? GROUP BY url ORDER BY cnt DESC",[Session::get('acc')]);
		$blogs = Blogs::where('acc',Session::get('acc'))->orderby('in','desc')->get();

		$cnt['in'] = Blogs::where('acc',Session::get('acc'))->sum('in');
		$cnt['out'] = Blogs::where('acc',Session::get('acc'))->sum('out');
		$pv = DB::select("SELECT count(*) as cnt FROM inalllog WHERE acc=?",[Session::get('acc')]);
		foreach($pv as $v){ $cnt['pv']=$v->cnt;}

		return View::make('refloop',array('show'=>$show,'blogs'=>$blogs,'anly'=>$anly,'cnt'=>$cnt));
	}

	public function refer($id){
		$show=1;
		$anly = DB::table('inalllog')->select(DB::raw('refer,count(*) as cnt'))->where('acc','=',Session::get('acc'))->where('url','=',"http://".$id."/")->groupby('refer')->orderby('cnt','desc')->paginate(50);
		return View::make('refer',array('show'=>$show,'id'=>$id,'refer'=>$anly));
	}

	public function referall($id){
		$show=1;
		$anly = DB::table('inalllog')->where('acc','=',Session::get('acc'))->where('url','=',"http://".$id."/")->orderby('utime','desc')->paginate(50);
		return View::make('referall',array('show'=>$show,'refer'=>$anly,'id'=>$id));
	}

	// ngword

	public function ngword(){
		$show = 1;
		$words = Ngword::where('sites_acc',Session::get('acc'))->get();
		return View::make('ngword.index',compact('words','show'));
	}

	public function ngwordEdit($id=0){
		$show = 1;
		if($id!=0){
			$word = Ngword::where('sites_acc',Session::get('acc'))->where('id',$id)->first();
		}else{
			$word = array('id'=>'','tag'=>'');
		}
		return View::make('ngword.edit',compact('word','show'));
	}

	public function ngwordPost(){
		$id = Input::get('id');
		$input = Input::except('_token','id');
		$input['sites_acc'] = Session::get('acc');

		$rules=array(
			'tag'=>'required'
		);
		//バリデーション処理
		$val=Validator::make($input,$rules);
		if($val->fails()){
			return Redirect::back()->withErrors($val)->withInput();
		}

		if(isset($id) && $id!=0){
			Ngword::where('sites_acc',Session::get('acc'))->where('id',$id)->update($input);
		}else{
			Ngword::create($input);
		}

		return Redirect::route('ngword');

	}

	public function ngwordDel($id){
		Ngword::where('sites_acc',Session::get('acc'))->where('id',$id)->delete();
		return Redirect::route('ngword');
	}

	// postword

	public function postword(){
		$show = 1;
		$words = Postword::where('sites_acc',Session::get('acc'))->get();
		return View::make('postword.index',compact('words','show'));
	}

	public function postwordEdit($id=0){
		$show = 1;
		if($id!=0){
			$word = Postword::where('sites_acc',Session::get('acc'))->where('id',$id)->first();
		}else{
			$word = array('id'=>'','tag'=>'');
		}
		return View::make('postword.edit',compact('word','show'));
	}

	public function postwordPost(){
		$id = Input::get('id');
		$input = Input::except('_token','id');
		$input['sites_acc'] = Session::get('acc');

		$rules=array(
			'tag'=>'required'
		);
		//バリデーション処理
		$val=Validator::make($input,$rules);
		if($val->fails()){
			return Redirect::back()->withErrors($val)->withInput();
		}

		if(isset($id) && $id!=0){
			Postword::where('sites_acc',Session::get('acc'))->where('id',$id)->update($input);
		}else{
			Postword::create($input);
		}

		return Redirect::route('postword');

	}

	public function postwordDel($id){
		Postword::where('sites_acc',Session::get('acc'))->where('id',$id)->delete();
		return Redirect::route('postword');
	}

	// actress

	public function actress(){
		$show = 1;
		$words = Actress::all();
		return View::make('actress.index',compact('words','show'));
	}

	public function actressEdit($id=0){
		$show = 1;
		if($id!=0){
			$word = Actress::where('id',$id)->first();
		}else{
			$word = array('id'=>'','name'=>'');
		}
		return View::make('actress.edit',compact('word','show'));
	}

	public function actressPost(){
		$id = Input::get('id');
		$input = Input::except('_token','id');

		$rules=array(
			'name'=>'required'
		);
		//バリデーション処理
		$val=Validator::make($input,$rules);
		if($val->fails()){
			return Redirect::back()->withErrors($val)->withInput();
		}

		if(isset($id) && $id!=0){
			Actress::where('id',$id)->update($input);
		}else{
			Actress::create($input);
		}

		return Redirect::route('actress');

	}

	public function actressDel($id){
		Actress::where('id',$id)->delete();
		return Redirect::route('actress');
	}

	// no actress

	public function noActress(){
		$show = 1;
		$words = Noactress::where('sites_acc',Session::get('acc'))->get();
		return View::make('noActress.index',compact('words','show'));
	}

	public function noActressEdit($id=0){
		$show = 1;
		if($id!=0){
			$word = Noactress::where('sites_acc',Session::get('acc'))->where('id',$id)->first();
		}else{
			$word = array('id'=>'','name'=>'','rate'=>'50');
		}
		return View::make('noActress.edit',compact('word','show'));
	}

	public function noActressPost(){
		$id = Input::get('id');
		$input = Input::except('_token','id');
		$input['sites_acc'] = Session::get('acc');

		$rules=array(
			'name'=>'required',
			'rate'=>'required'
		);
		//バリデーション処理
		$val=Validator::make($input,$rules);
		if($val->fails()){
			return Redirect::back()->withErrors($val)->withInput();
		}

		if(isset($id) && $id!=0){
			Noactress::where('sites_acc',Session::get('acc'))->where('id',$id)->update($input);
		}else{
			Noactress::create($input);
		}

		return Redirect::route('noActress');

	}

	public function noActressDel($id){
		Noactress::where('sites_acc',Session::get('acc'))->where('id',$id)->delete();
		return Redirect::route('noActress');
	}

}