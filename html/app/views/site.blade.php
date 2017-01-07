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
		<div class="row">
			<div class="col-xs-3">
			  <div class="btn btn-default"><a href="{{route('site.edit')}}">サイト追加</a></div>
			</div>
			<div class="col-xs-5 col-xs-offset-4 text-right">
				{{Form::open(['url'=>route('site') ,'method'=>'get'])}}
				{{Form::select('category',['*'=>'全選択']+Config::get('app.category'),Input::get('category'))}}
				{{Form::select('sitetype',['*'=>'全選択']+Config::get('app.sitetype'),Input::get('sitetype'))}}
				{{Form::submit('絞込')}}
				{{Form::close()}}
			</div>
		</div>

		<table class="tablesorter table table-striped" width="100%" border="0">
			<tr>
				<td>ID</td>
				<td>種別</td>
				<td>サイト名称</td>
				<td>カテゴリ</td>
				<td>操作</td>
			</tr>
				@if(count($sites) > 0)
				@foreach($sites as $site)
			<tr>
				<td>{{$site->id}}</td>
				<td>{{array_get(Config::get('app.sitetype'),$site->sitetype)}}</td>
				<td><a href="{{$site->url}}" rel="nofollow">{{$site->name}}</a></td>
				<td>{{array_get(Config::get('app.category'),$site->category)}}</td>
				<td>
					<a href="/rss/site/edit/{{$site->id}}"><span class="glyphicon glyphicon-cog"></span></a>
					<a class="confirm" href="/rss/site/del/{{$site->id}}"><span class="glyphicon glyphicon-trash"></span></a>
				</td>
			<tr>
				@endforeach
				@else
			<tr><td colspan="5">該当サイトがありません</td></tr>
				@endif
		</table>
	<!--/.panel-body--></div>
<!--/.panel--></div>
@stop