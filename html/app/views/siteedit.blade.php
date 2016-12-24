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
    @if ($site['id']!=0)
    {{Form::open(['url'=>'/rss/site','method'=>'post'])}}
    {{Form::hidden('acc',$site['acc'])}}
    {{Form::submit('設定詳細',['class'=>'btn btn-info'])}}
    {{Form::close()}}
    @endif
		{{ Form::open(array('url' => '/rss/site/edit', 'method' => 'post'))}}
		{{Form::hidden('id',$site['id'])}}
		@if ($site['id']==0)
		{{Form::textField('acc','アカウント名',$site['acc'])}}
		@else
		{{Form::hidden('acc',$site['acc'])}}
		<div class="alert alert-warning" role="alert">編集中のサイトアカウント：{{$site['acc']}}</div>
		@endif

    {{Form::label('sitetype','種別')}}
    {{Form::rbinline('sitetype','',Config::get('app.sitetype'),$site['sitetype'] or 1)}}

    {{Form::label('post_status','WP投稿状態')}}
    {{Form::rbinline('post_status','',Config::get('app.post_status'),$site['post_status'])}}

    {{Form::selectField('category','カテゴリ &raquo; <small>管理画面で利用</small>',Config::get('app.category'),$site['category'])}}

		{{Form::textField('name','サイト名',$site['name'])}}
    {{Form::textField('url','サイトURL',$site['url'])}}
		{{Form::label('isactive','サイト状態(RSS取得)')}}
    {{Form::rbinline('isactive','',array(0=>'無効',1=>'有効'),$site['isactive'])}}

    {{Form::textField('actressFormat','女優見出し設定 <small class="label label-danger">未記入時はタイトルの先頭に女優名を表示しません(例)【#actress#】</small>',$site['actressFormat'])}}
    {{Form::textField('titleLength','投稿タイトル文字数制限 <small class="label label-danger">この文字数を超えたタイトルは投稿しません</small>',($site['titleLength']!='')?$site['titleLength']:100,['style'=>"width:60px"])}}

    {{Form::label('postType','投稿方法')}}
    {{Form::rbinline('postType','',array(0=>'投稿しない',1=>'動画投稿(リアルタイム)'),$site['postType'])}}

    <!-- Nav tabs -->
    <ul class="nav nav-tabs" role="tablist">
      <li class="active"><a href="#wp" role="tab" data-toggle="tab">WP設定</a></li>
      <li><a href="#movdefault" role="tab" data-toggle="tab">動画タグ設定</a></li>
    </ul>

    <!-- Tab panes -->
    <div class="tab-content" style="margin-top:15px">
      <div class="tab-pane active" id="wp">
        <div class="alert alert-info" role="alert">投稿先ブログAPI設定</div>
          <blockquote class="blockquote-reverse">WPの場合、通常　http://hoge.com/xmlrpc.php です。<br> FC2の場合、http://blog.fc2.com/xmlrpc.php です</blockquote>

          {{Form::selectField('blogType','ブログ種別',Config::get('app.blogType'),$site['blogType'],['id'=>'blogtype'])}}

          <div id="hostarea">
          {{Form::textField('wphost','ホスト wp→http://hoge.com/xmlrpc.php',$site['wphost'],['id'=>'host'])}}
          </div>
          {{Form::textField('wpuser','ユーザ名',$site['wpuser'])}}
          {{Form::textField('wppass','パスワード | APIキー',$site['wppass'])}}

          {{Form::textField('wpdbhost','WP DBホスト &raquo; All IN ONE用 (dbname=WPデータベース名;host=WPのドメイン or IPアドレス)',$site['wpdbhost'])}}
          <p style="color:red">↑該当WPのDBにアクセス可能な右記アカウントが必要 wpaioseo/Bb8YwdfAcuaP2EPI</p><br>

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
        {{Form::textField('catid','カテゴリID<br>FC2の場合0,5のように記載するとランダムカテゴリ指定<br>WPの場合 xhamster/fc2/xvideoカテゴリ(slug必須)を自動選択投稿、マッチしない場合カテゴリID=1で投稿(設定内容は関係なし)',$site['catid'])}}

        {{Form::textField('seotitle','SEO Pack title - #title# / #imgurl# / #url#',$site['seotitle'])}}
        {{Form::textField('seodesc','SEO Pack description - #title# / #imgurl# / #url#',$site['seodesc'])}}
        {{Form::textField('seokeyword','SEO Pack keyword - #title# / #imgurl# / #url#',$site['seokeyword'])}}
      </div>

      <div class="tab-pane" id="movdefault">

        @foreach(Config::get('app.movService') as $_name=>$_alias)
        <div class="panel panel-default">
          <div class="panel-heading">{{$_alias}}</div>
          <div class="panel-body">
            {{Form::textareaField($_name,'本文 #title# / #imgurl# / #url# / #movid# / #movSite#',$site[$_name])}}
          </div>
        </div>
        @endforeach
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

@section('js')
<script>
$(function(){
  $(document).on('change','#blogtype',function(){
    var blogtype = $("#blogtype").val();
    if(blogtype!='wp'){
      $("#hostarea").hide();
    }else{
      $("#hostarea").show();
    }
  })
  var blogtype = $("#blogtype").val();
  if(blogtype!='wp')
    $("#hostarea").hide();

});
</script>

@stop