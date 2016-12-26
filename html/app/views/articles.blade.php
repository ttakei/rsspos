@extends('layout.admin')

@section('side')
	@parent
@stop

@section('content')
<style>
#blogList td{
	font-size:13px;
}
</style>
<div class="panel panel-primary">
	<div class="panel-heading">記事一覧</div>
	<div class="panel-body">
		{{$articles->links()}}
		<table id="blogList" class="tablesorter table table-striped" width="100%" border="0">
			<tr>
				<td>ID</td>
				<td>取得元 | 記事時間 | 投稿時間 | 動画サービス</td>
			</tr>
				@if(count($articles) > 0)
				@foreach($articles as $item)
			<tr>
				<td>{{$item->id}}</td>
				<td><a href="{{$item->url}}" target="_blank">{{$item->name}}</a> | {{$item->created_at}} | <span {{($item->posted_at!='0000-00-00 00:00:00'&&$item->movSite!='')?'style="color:red"':''}}>{{$item->posted_at}}</span> | {{$item->movSite!=''?$item->movSite:'none'}} {{($item->movlink!='')?'link':''}} <br>
				前：{{$item->title_org}}<br>後：{{$item->title}}</td>
			<tr>
				@endforeach
				@else
			<tr><td colspan="6">記事がありません</td></tr>
				@endif
		</table>
	<!--/.panel-body--></div>
<!--/.panel--></div>
@stop

@section('js')
@stop