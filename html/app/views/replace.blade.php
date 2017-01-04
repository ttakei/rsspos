@extends('layout.admin')

@section('side')
  @parent
@stop

@section('content')
<div class="panel panel-primary">
  <div class="panel-heading">置換文字列一覧 @if($_SERVER['SERVER_ADDR']=='153.120.34.241') <span class="label label-warning">サイト毎の設定となります</span>@if(Session::has('acc'))<span class="label label-success">{{Session::get('acc')}}</span> @else <span class="label label-danger">サイトが選択されていません</span>@endif @endif</div>
  <div class="panel-body">
    <div class="text-left clr10">
    <div class="btn btn-default"><a href="/replace/edit">新規追加</a></div>
    </div>
    <table id="blogList" class="tablesorter table table-striped" width="100%" border="0">
      <tr>
        <td>置換前文字列</td>
        <td>置換後文字列</td>
        @if($_SERVER['SERVER_ADDR']=='153.120.34.241')
        <td>対象サイト</td>
        @endif
        <td>更新日時</td>
        <td>操作</td>
      </tr>
        @if(count($words) > 0)
        @foreach($words as $word)
      <tr>
        <td>{{$word->from}}</td>
        <td>{{$word->to}}</td>
        @if($_SERVER['SERVER_ADDR']=='153.120.34.241')
        <td>{{$word->name}} / {{$word->acc}}</td>
        @endif
        <td>{{$word->updated_at}}</td>
        <td>
          <a href="/replace/edit/{{$word->id}}"><span class="glyphicon glyphicon-cog"></span></a>
          <!-- <a href="#"><span class="glyphicon glyphicon-signal"></span></a> -->
          <a class="confirm" href="/replace/delete/{{$word->id}}"><span class="glyphicon glyphicon-trash"></span></a>
        </td>
      <tr>
        @endforeach
        @else
      <tr><td colspan="6">置換文字列がありません</td></tr>
        @endif
    </table>
  <!--/.panel-body--></div>
<!--/.panel--></div>
@stop

@section('js')
@stop