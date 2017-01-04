@extends('layout.admin')

@section('side')
<div class="list-group">
	<a href="/rss/site" class="list-group-item">サイト</a>
</div>
@stop

@section('content')
<div class="panel panel-info">
	<div class="panel-heading">サイト@if ($site['id']!=0)情報変更@else追加@endif</div>
	<div class="panel-body">
		{{ Form::open(array('url' => '/rss/site/edit', 'method' => 'post'))}}
		{{Form::hidden('id',$site['id'])}}
		@if ($site['id']==0)
		{{Form::textField('acc','アカウント名',$site['acc'])}}
		@else
		{{Form::hidden('acc',$site['acc'])}}
		<div class="alert alert-warning" role="alert">{{$site['acc']}}</div>
		@endif
		{{Form::textField('name','サイト名',$site['name'])}}
		{{Form::label('isactive','サイト状態')}}
        {{Form::rbinline('isactive','',array(0=>'無効',1=>'有効'),$site['isactive'])}}

		{{Form::label('postType','投稿方法')}}
        {{Form::rbinline('postType','',array(0=>'WP投稿(タイムテーブル利用)',1=>'動画投稿(リアルタイム)'),$site['postType'])}}

		{{Form::textareaField('tags','タグ一覧(,区切り)',$site['tags'],['rows'=>3])}}

<!-- Nav tabs -->
<ul class="nav nav-tabs" role="tablist">
  <li class="active"><a href="#wp" role="tab" data-toggle="tab">WP設定</a></li>
  <li><a href="#movdefault" role="tab" data-toggle="tab">該当なし</a></li>
  <li><a href="#xvideo" role="tab" data-toggle="tab">xvideo</a></li>
  <li><a href="#fc2" role="tab" data-toggle="tab">FC2</a></li>
  <li><a href="#fc2ja" role="tab" data-toggle="tab">FC2JA</a></li>
  <li><a href="#xhamster" role="tab" data-toggle="tab">xhamster</a></li>
  <li><a href="#redtube" role="tab" data-toggle="tab">redtube</a></li>
  <li><a href="#erovideonet" role="tab" data-toggle="tab">erovideonet</a></li>
  <li><a href="#pornhub" role="tab" data-toggle="tab">pornhub</a></li>
  <li><a href="#pipii" role="tab" data-toggle="tab">pipii</a></li>
</ul>

<!-- Tab panes -->
<div class="tab-content" style="margin-top:15px">
  <div class="tab-pane active" id="wp">
		<div class="alert alert-info" role="alert">WP/FC2設定</div>
		<blockquote class="blockquote-reverse">WPの場合、通常　http://hoge.com/xmlrpc.php です。<br>
		FC2の場合、http://blog.fc2.com/xmlrpc.php です</blockquote>

		{{Form::textField('wphost','ホスト',$site['wphost'])}}
		{{Form::textField('wpuser','ユーザ名',$site['wpuser'])}}
		{{Form::textField('wppass','パスワード',$site['wppass'])}}

		{{--Form::textField('wpdbhost','WP DBホスト <small>dbname=WPデータベース名;host=WPのドメイン or IPアドレス;user=WPDBアカウント;pass=WPDBパスワード</small>',$site['wpdbhost'])--}}

		<div class="panel panel-success">
			<div class="panel-heading">rssweb.net用仕様</div>
			<div class="panel-body">
				{{Form::label('isEyecatch','画像投稿&アイキャッチ設定(WPのみ）')}}
				{{Form::rb('isEyecatch',array(0=>'無効',1=>'有効'),$site['isEyecatch'])}}

				{{Form::label('isNeedmov','投稿に記事中の動画必須')}}
				{{Form::rb('isNeedmov',array(0=>'不必要',1=>'必須'),$site['isNeedmov'])}}

				{{Form::label('isNeedimg','投稿にRSSの画像必須')}}
				{{Form::rb('isNeedimg',array(0=>'不必要',1=>'必須'),$site['isNeedimg'])}}
			</div>
		</div>

		<div class="alert alert-info" role="alert">投稿内容</div>
		{{Form::textField('wptitle','タイトル',$site['wptitle'])}}
		{{Form::textareaField('wpdesc','本文 - #title# / #imgurl# / #url# / #content#(スクレイピングしたデータ)',$site['wpdesc'])}}
		@if($_SERVER['SERVER_ADDR']=='133.242.20.188')
		{{Form::textField('catid','カテゴリID<br>FC2の場合0,5のように記載するとランダムカテゴリ指定<br>WPの場合 xhamster/fc2/xvideoカテゴリ(slug必須)を自動選択投稿、マッチしない場合カテゴリID=1で投稿(設定内容は関係なし)',$site['catid'])}}
		@else
		{{Form::textField('catid','カテゴリID(FC2のみ 0,5のように記載するとランダムカテゴリ指定)',$site['catid'])}}
		@endif

		{{--Form::textField('seotitle','SEO Pack title - #title# / #imgurl# / #url#',$site['seotitle'])--}}
		{{--Form::textField('seodesc','SEO Pack description - #title# / #imgurl# / #url#',$site['seodesc'])--}}
		{{--Form::textField('seokeyword','SEO Pack keyword - #title# / #imgurl# / #url#',$site['seokeyword'])--}}
  </div>
  <div class="tab-pane" id="movdefault">
		{{Form::textareaField('movdefault','本文 #title# / #imgurl# / #url# / #movid# / #movSite#',$site['movdefault'])}}
  </div>
  <div class="tab-pane" id="xvideo">
		{{Form::textareaField('xvideo','本文 #title# / #imgurl# / #url# / #movid# / #movSite#',$site['xvideo'])}}
  </div>
  <div class="tab-pane" id="fc2">
		{{Form::textareaField('fc2','本文 #title# / #imgurl# / #url# / #movid# / #movSite#',$site['fc2'])}}
  </div>
  <div class="tab-pane" id="fc2ja">
		{{Form::textareaField('fc2ja','本文 #title# / #imgurl# / #url# / #movid# / #movSite#',$site['fc2ja'])}}
  </div>
  <div class="tab-pane" id="xhamster">
		{{Form::textareaField('xhamster','本文 #title# / #imgurl# / #url# / #movid# / #movSite#',$site['xhamster'])}}
  </div>
  <div class="tab-pane" id="redtube">
        {{Form::textareaField('redtube','本文 #title# / #imgurl# / #url# / #movid# / #movSite#',$site['redtube'])}}
  </div>
  <div class="tab-pane" id="erovideonet">
        {{Form::textareaField('erovideonet','本文 #title# / #imgurl# / #url# / #movid# / #movSite#',$site['erovideonet'])}}
  </div>
  <div class="tab-pane" id="pornhub">
        {{Form::textareaField('pornhub','本文 #title# / #imgurl# / #url# / #movid# / #movSite#',$site['pornhub'])}}
  </div>
  <div class="tab-pane" id="pipii">
        {{Form::textareaField('pipii','本文 #title# / #imgurl# / #url# / #movid# / #movSite#',$site['pipii'])}}
  </div>
</div>




		@if ($site['id']==0)
		{{Form::submit('追加',array('class'=>'btn btn-danter'))}}
		@else
		{{Form::submit('変更',array('class'=>'btn btn-danter'))}}
		@endif

		{{ Form::close()}}
	</div>
</div>
@stop