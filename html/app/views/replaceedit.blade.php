@extends('layout.admin')

@section('content')
<div class="panel panel-primary">
  <div class="panel-heading">置換文字列設定</div>
  <div class="panel-body">
    {{Form::open(array('url'=>'/replace/edit'))}}
    {{Form::hidden('id',$word['id'])}}
    @if($_SERVER['SERVER_ADDR']=='153.120.34.241')
    {{Form::hidden('site_acc',Session::get('acc'))}}
    @endif
    {{Form::textField('from','置換前文字列',$word['from'],array('id'=>'from'))}}
    {{Form::textField('to','置換後文字列',$word['to'],array('id'=>'to'))}}
    {{Form::submit('保存',array('class'=>'btn btn-primary'))}}
    {{Form::close()}}
  <!--/.panel-body--></div>
<!--/.panel--></div>
@stop

@section('js')
@stop