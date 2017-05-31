@extends('layout.admin')

@section('side')
@stop

@section('content')
<div class="panel panel-primary">
  <div class="panel-heading">ユーザ編集</div>
  <div class="panel-body">
    @include('parts.message')
    {{Form::open(['url'=>route('admin.user.update')])}}
    {{Form::hidden('id',$user->id)}}
    {{Form::textField('nickname','ニックネーム',$user->nickname)}}
    {{Form::textField('email','メールアドレス',$user->email)}}
    {{Form::textField('password','パスワード',$user->password)}}
   
    {{Form::cb('sites[]','編集サイト',Sites::all()->lists('name','id'),$user->sites->lists('site_id'))}}
    {{Form::label('role','権限')}}
    {{Form::rb('role',array('admin'=>'admin','writer'=>'writer'),$user->role)}}
    {{Form::submit('保存',array('class'=>'btn btn-primary'))}}
    {{Form::close()}}
  </div>
</div>
@endsection
