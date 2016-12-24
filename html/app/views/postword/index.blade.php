@extends('layout.admin')

@section('side')
  @parent
@stop

@section('content')
<div class="panel panel-primary">
  <div class="panel-heading">投稿ワード一覧 <small>このワードがタイトルに有る場合のみ投稿します</small></div>
  <div class="panel-body">
    <div class="text-left clr10">
    <div class="btn btn-default"><a href="{{route('postword.edit')}}">新規追加</a></div>
    </div>
    <table id="blogList" class="tablesorter table table-striped" width="100%" border="0">
      <tr>
        <td>投稿ワード</td>
        <td>更新日時</td>
        <td>操作</td>
      </tr>
        @if(count($words) > 0)
        @foreach($words as $word)
      <tr>
        <td>{{$word->tag}}</td>
        <td>{{$word->updated_at}}</td>
        <td>
          <a href="{{route('postword.edit',[$word->id])}}"><span class="glyphicon glyphicon-cog"></span></a>
          <a class="confirm" href="{{route('postword.del',[$word->id])}}"><span class="glyphicon glyphicon-trash"></span></a>
        </td>
      <tr>
        @endforeach
        @else
      <tr><td colspan="6">投稿ワードがありません</td></tr>
        @endif
    </table>
  <!--/.panel-body--></div>
<!--/.panel--></div>
@stop

@section('js')
@stop