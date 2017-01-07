@extends('layout.admin')

@section('side')
@stop

@section('content')
<div class="panel panel-primary">
  <div class="panel-heading">ユーザ一覧</div>
  <div class="panel-body">
    @include('parts.message')
    <div class="text-left clr10">
    <div class="btn btn-default"><a href="{{route('admin.user.create')}}">ユーザ追加</a></div>
    </div>
    <table id="blogList" class="tablesorter table table-striped" width="100%" border="0">
      <tr>
        <td>ユーザID</td>
        <td>メールアドレス</td>
        <td>パスワード</td>
        <td>操作サイト</td>
        <td>権限</td>
        <td>操作</td>
      </tr>
        @if(count($userObj) > 0)
        @foreach($userObj as $user)
      <tr>
        <td>{{$user->id}}</td>
        <td>{{$user->email}}</td>
        <td>{{$user->password}}</td>
        <td>
        @foreach($user->sites as $usite)
          {{$usite->site->name or ''}}<br>
        @endforeach
        <td>{{$user->role}}</td>
        </td>
        <td>
          <a href="{{route('admin.user.edit',['id'=>$user->id])}}"><span class="glyphicon glyphicon-cog"></span></a>
          <a class="confirm" href="{{route('admin.user.delete',['id'=>$user->id])}}"><span class="glyphicon glyphicon-trash"></span></a>
        </td>
      <tr>
        @endforeach
        @else
      <tr><td colspan="6">ユーザがいません</td></tr>
        @endif
    </table>
  <!--/.panel-body--></div>
<!--/.panel--></div>
@stop

@section('js')
@stop