@extends('layout.admin')

@section('side')
  @parent
@stop

@section('content')
<div class="panel panel-primary">
  <div class="panel-heading">置換文字列一覧</div>
  <div class="panel-body">
    <div class="text-left clr10">
    <div class="btn btn-default"><a href="{{route('replace.edit')}}">新規追加</a></div>
    </div>
    <table id="blogList" class="tablesorter table table-striped" width="100%" border="0">
      <tr>
        <td>置換前文字列</td>
        <td>置換後文字列</td>
        <td>更新日時</td>
        <td>操作</td>
      </tr>
        @if(count($words) > 0)
        @foreach($words as $word)
      <tr>
        <td>{{$word->from}}</td>
        <td>{{$word->to}}</td>
        <td>{{$word->updated_at}}</td>
        <td>
          <a href="{{route('replace.edit',[$word->id])}}"><span class="glyphicon glyphicon-cog"></span></a>
          <!-- <a href="#"><span class="glyphicon glyphicon-signal"></span></a> -->
          <a class="confirm" href="{{route('replace.del',[$word->id])}}"><span class="glyphicon glyphicon-trash"></span></a>
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