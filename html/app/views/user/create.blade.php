@extends('layout.admin')

@section('side')
@stop

@section('content')
<div class="panel panel-primary">
	<div class="panel-heading">ユーザ追加</div>
	<div class="panel-body">
		@include('parts.message')
		{{Form::open(['url'=>route('admin.user.store')])}}
		{{Form::textField('nickname','ニックネーム',Input::old('nickname'))}}
		{{Form::textField('email','メールアドレス',Input::old('email'))}}
		{{Form::textField('password','パスワード',Input::old('password'))}}
		{{Form::cb('sites[]','編集サイト',Sites::all()->lists('name','id'),null)}}
		{{Form::label('role','権限')}}
		{{Form::rb('role',array('admin'=>'admin','writer'=>'writer'),'writer')}}
		{{Form::submit('保存',array('class'=>'btn btn-primary'))}}
		{{Form::close()}}
	</div>
</div>
@endsection
