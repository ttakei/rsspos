@extends('layout.admin')

@section('content')
<div class="panel panel-primary">
  <div class="panel-heading">置換文字列設定</div>
  <div class="panel-body">
    {{Form::open(array('url'=>'/rss/replace/edit'))}}
    {{Form::hidden('id',$word['id'])}}
    {{Form::hidden('site_acc',Session::get('acc'))}}
    {{Form::textField('from','置換前文字列',$word['from'],array('id'=>'from'))}}
    {{Form::textField('to','置換後文字列(,区切り)',$word['to'],array('id'=>'to'))}}
    {{Form::submit('保存',array('class'=>'btn btn-primary'))}}
    {{Form::close()}}
  <!--/.panel-body--></div>
<!--/.panel--></div>
@stop

@section('js')
@stop