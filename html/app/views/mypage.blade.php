@extends('layout.admin')

@section('side')
	<div class="list-group">
		<a href="/mypage" class="list-group-item active">マイページ</a>
		<a href="#inline_content" class="inline list-group-item">アカウント</a>
	</div>

	<div class="clr10"></div>
	<div class="panel panel-warning info">
		<div class="panel-heading">新着情報</div>
		<div class="panel-body">
			<dl>
				@if(count($news) > 0)
				@foreach($news as $v)
				<dt>{{date('Y-m-d',strtotime($v->created))}}</dt>
				<dd>{{ $v->title }}</dd>
				@endforeach
				@else お知らせはありません。
				@endif
			</dl>
		</div>
	</div>
@stop

@section('content')
<div class="panel panel-info">
	<div class="panel-heading">お知らせ</div>
	<div class="panel-body">
		<dl>
			@if(count($news) > 0)
			@foreach($news as $v)
			<dt>{{ $v->title }}({{date('Y-m-d',strtotime($v->created))}})</dt>
			<dd>{{ $v->desc }}</dd>
			@endforeach
			@else お知らせはありません。
			@endif
		</dl>
	</div>
</div>
<div class="panel panel-primary">
	<div class="panel-heading">アクセス解析タグ関連</div>
	<div class="panel-body">
	<div class="well">
&lt;script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js">&lt;/script><br>
&lt;script src="http://{{$_SERVER['HTTP_HOST']}}/rank.php?id={{$cfg['acc']}}">&lt;/script></div>
上記２行を設置するサイトに記載してください。


	</div>
</div>

<div style='display:none'>
<div id='inline_content' style='padding:10px; background:#fff;'>
<div class="panel panel-primary">
	<div class="panel-heading">ユーザ情報</div>
	<div class="panel-body">
	{{ Form::open(array('url' => '/mypage/edit', 'method' => 'post'))}}
		{{Form::hidden('id',$user['id'])}}
		<table class="tablesorter table table-striped" width="100%" border="0">
			<tr>
				<td>メールアドレス</td>
				<td><input type="email" name="email" class="form-control" id="inputEmail" value="{{$user['email']}}"></td>
			</tr>
			<tr>
				<td>ニックネーム</td>
				<td>{{ Form::text('nickname',$user['nickname'],array('class'=>'form-control')) }}</td>
			</tr>
			<tr>
				<td>生年月日</td>
				<td>{{Form::text('birth',$user['birth'],array('class'=>'form-control'))}}</td>
			</tr>
			<tr>
				<td>パスワード</td>
				<td>{{Form::text('password',$user['password'],array('class'=>'form-control'))}}</td>
			</tr>
			<tr>
				<td colspan=2><input type='submit' class="btn btn-danger" value="変更"></td>
			</tr>
		</table>
		{{ Form::close()}}
	</div>
</div>
</div>
</div>
@stop