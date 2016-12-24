@extends('layout.admin')

@section('content')
<div class="panel panel-primary">
  <div class="panel-heading">投稿ワード設定 <small>このワードがタイトルに有る場合のみ投稿します</small></div>
  <div class="panel-body">
    {{Form::open(array('url'=>'/rss/postword/edit'))}}
    {{Form::hidden('id',$word['id'])}}
    {{Form::textField('tag','文字',$word['tag'],array('id'=>'tag'))}}
    {{Form::submit('保存',array('class'=>'btn btn-primary'))}}
    {{Form::close()}}
  <!--/.panel-body--></div>
<!--/.panel--></div>
@stop

@section('js')
@stop