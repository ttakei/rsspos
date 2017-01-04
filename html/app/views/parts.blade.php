@extends('layout.admin')

@section('side')
	@parent
@stop

@section('content')
<div class="panel panel-primary">
	<div class="panel-heading">パーツ一覧</div>
	<div class="panel-body">
		<div class="text-left clr10">
	  <div class="btn btn-default"><a href="{{route('partsedit')}}">パーツ追加</a></div>
	  </div>
		<table class="tablesorter table table-striped" width="100%" border="0">
			<tr>
				<td width=20>id</td>
				<td width=100>名称</td>
				<td width=80>操作</td>
			</tr>
				@if(count($parts) > 0)
				@foreach($parts as $part)
			<tr>
				<td>{{$part->id}}</td>
				<td>{{$part->name}}</a></td>
				<td>
					<a href="/rss/parts/edit/{{$part->id}}"><span class="glyphicon glyphicon-cog"></span></a>
					<a class="confirm" href="/rss/parts/del/{{$part->id}}"><span class="glyphicon glyphicon-trash"></span></a>
				</td>
			<tr>
				@endforeach
				@else
			<tr><td colspan="6">パーツがありません</td></tr>
				@endif
		</table>
	<!--/.panel-body--></div>
<!--/.panel--></div>
@stop