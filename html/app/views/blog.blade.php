@extends('layout.admin')

@section('side')
	@parent
@stop

@section('content')
<div class="panel panel-primary">
	<div class="panel-heading">ブログ一覧</div>
	<div class="panel-body">
		<div class="text-left clr10">
	  <div class="btn btn-default"><a href="{{route('blog.edit')}}">ブログ追加</a></div>
	  </div>
		<table id="blogList" class="tablesorter table table-striped" width="100%" border="0">
			<tr>
				<td>ID</td>
				<td>サイト名称</td>
				<td>操作</td>
			</tr>
				@if(count($blogs) > 0)
				@foreach($blogs as $blog)
			<tr>
				<td>{{$blog->id}}</td>
				<td><a href="{{$blog->siteurl}}" target="_blank">{{$blog->name}}</a></td>
				<td>
					<a href="/rss/blog/edit/{{$blog->id}}"><span class="glyphicon glyphicon-cog"></span></a>
					<a class="confirm" href="/rss/blog/del/{{$blog->id}}"><span class="glyphicon glyphicon-trash"></span></a>
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

@section('js')
@stop