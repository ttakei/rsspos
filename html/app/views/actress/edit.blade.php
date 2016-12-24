@extends('layout.admin')

@section('content')
<div class="panel panel-primary">
  <div class="panel-heading">女優辞書設定</div>
  <div class="panel-body">
    <div class="alert alert-success">女優名の辞書登録を行います。</div>
    {{Form::open(array('url'=>'/rss/actress/edit'))}}
    {{Form::hidden('id',$word['id'])}}
    {{Form::textField('name','女優名',$word['name'],array('id'=>'name'))}}
    {{Form::submit('保存',array('class'=>'btn btn-primary'))}}
    {{Form::close()}}
  <!--/.panel-body--></div>
<!--/.panel--></div>
@stop

@section('js')
@stop