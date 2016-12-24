@extends('layout.admin')

@section('content')
<div class="panel panel-primary">
  <div class="panel-heading">NON女優辞書設定</div>
  <div class="panel-body">
    <div class="alert alert-success">女優名を先頭に表示する設定になっている場合かつタイトルに女優名が見つからない場合、設定された確率で文字列をピックアップします</div>
    {{Form::open(array('url'=>'/rss/noActress/edit'))}}
    {{Form::hidden('id',$word['id'])}}
    {{Form::textField('name','表示文字列',$word['name'],array('id'=>'name'))}}
    {{Form::textField('rate','割合',$word['rate'],array('id'=>'rate'))}}
    {{Form::submit('保存',array('class'=>'btn btn-primary'))}}
    {{Form::close()}}
  <!--/.panel-body--></div>
<!--/.panel--></div>
@stop

@section('js')
@stop