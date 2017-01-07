@extends('layout.base')

@section('side')
<div class="side col-lg-2">
<a href="#" class="btn btn-success col-lg-12">新規登録</a>
<div class="clr10"></div>
<a href="#inline_content" class="btn btn-warning col-lg-12 inline">ログイン</a><br>
<div class="clr10"></div>
<div class="panel panel-primary">
  <div class="panel-heading">新着情報</div>
  <div class="panel-body">
    12/26日動画リンク取得に対応しました。
  </div>
</div>
</div>
<div style='display:none'>
	<div id='inline_content' style='padding:10px; background:#fff;'>
		{{ Form::open(array('url' => 'login', 'method' => 'post'))}}
		<div class="alert alert-info">ユーザ情報を入力し、ログインボタンを押して下さい</div>
		<label for="inputEmail">メールアドレス</label>
		<input type="email" name="email" class="form-control" id="inputEmail" placeholder="メールアドレス" value="{{$cookie['email']}}">

		<div class="clr10"></div>

		<label for="inputPwd">パスワード</label>
		<input type="password" name="password" class="form-control" id="inputPwd" placeholder="パスワード" value="{{$cookie['password']}}">

		<div class="clr10"></div>

		<input type="checkbox" name='remember' value="1" 
		@if($cookie['remember']==1)
		checked
		@else
		@endif
		>ログイン状態を保存する　　

		<input type='submit' class="btn btn-danger" value="ログイン">
<!-- 		<div class="btn btn-danger" onclick="$.colorbox.close(); return false;">ログイン</div>-->
{{ Form::close()}}
	</div>
</div>
@stop

@section('content')
<div class="content col-lg-10">
<div class="well well-success">RSS Widget システム</div>
</div>
@stop