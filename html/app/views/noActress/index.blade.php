@extends('layout.admin')

@section('side')
  @parent
@stop

@section('content')
<div class="panel panel-primary">
  <div class="panel-heading">NON女優辞書一覧</div>
  <div class="panel-body">
    <div class="alert alert-success">女優名を先頭に表示する設定になっている場合かつタイトルに女優名が見つからない場合、設定された確率で文字列をピックアップします</div>
    <div class="text-left clr10">
      <div class="btn btn-default"><a href="{{route('noActress.edit')}}">新規追加</a></div>
    </div>
    <table id="blogList" class="tablesorter table table-striped" width="100%" border="0">
      <tr>
        <td>NON女優辞書</td>
        <td>レート</td>
        <td>更新日時</td>
        <td>操作</td>
      </tr>
        @if(count($words) > 0)
        @foreach($words as $word)
      <tr>
        <td>{{$word->name}}</td>
        <td>{{$word->rate}}</td>
        <td>{{$word->updated_at}}</td>
        <td>
          <a href="{{route('noActress.edit',[$word->id])}}"><span class="glyphicon glyphicon-cog"></span></a>
          <a class="confirm" href="{{route('noActress.del',[$word->id])}}"><span class="glyphicon glyphicon-trash"></span></a>
        </td>
      <tr>
        @endforeach
        @else
      <tr><td colspan="6">NON女優辞書がありません</td></tr>
        @endif
    </table>
  <!--/.panel-body--></div>
<!--/.panel--></div>
@stop

@section('js')
@stop