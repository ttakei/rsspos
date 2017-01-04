@extends('layout.admin')

@section('side')
<div class="list-group">
	<a href="/rss/site" class="list-group-item">サイト管理</a>
</div>
@stop

@section('content')
<div class="panel panel-primary">
	<div class="panel-heading">サイト一覧</div>
	<div class="panel-body">
	  <div class="btn btn-default"><a href="{{route('siteedit')}}">サイト追加</a></div>
		<table class="tablesorter table table-striped" width="100%" border="0">
			<tr>
				<td>ID</td>
				<td>アカウント名</td>
				<td>サイト名称</td>
				<td>操作</td>
			</tr>
				@if(count($sites) > 0)
				@foreach($sites as $site)
			<tr>
				<td>{{$site->id}}</td>
				<td>{{$site->acc}}</td>
				<td>{{$site->name}}</td>
				<td>
					<a href="/rss/site/edit/{{$site->id}}"><span class="glyphicon glyphicon-cog"></span></a>
					<a class="confirm" href="/rss/site/del/{{$site->id}}"><span class="glyphicon glyphicon-trash"></span></a>
				</td>
			<tr>
				@endforeach
				@else
			<tr><td colspan="6">管理サイトがありません</td></tr>
				@endif
		</table>
	<!--/.panel-body--></div>
<!--/.panel--></div>
@stop