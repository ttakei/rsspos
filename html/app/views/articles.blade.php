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
				<td>画像</td>
				<td>取得元 | 取得時間 | {{Config::get('app.manu')?'予約時間 | ':''}}投稿時間</td>
				@if(Config::get('app.manu'))
				<td>編集</td>
				@endif
			</tr>
			@if(count($articles) > 0)
			@foreach($articles as $item)
			<tr>
				<td>{{$item->id}}</td>
				<td>@if($item->imgurl!='')<img src="{{$item->imgurl}}" width=200>@endif</td>
				<td>
					<a href="{{$item->url}}" target="_blank">{{$item->name}}</a> | <p class="label label-success">{{$item->movSite}}{{($item->movlink!='')?' (link)':''}}</p><br>
					{{$item->created_at}} | 
					@if(Config::get('app.manu'))
					<span style="{{($item->researved_at!='0000-00-00 00:00:00')?'background-color:#FBB':''}}">{{$item->researved_at}}</span> | 
					@endif
					<span style="{{($item->posted_at!='0000-00-00 00:00:00')?'background-color:#BBB':''}}">{{$item->posted_at}}</span><br>
					前：{{$item->title_org}}<br>後：{{$item->title}}
				</td>
				@if(Config::get('app.manu'))
				<td>
					<a href="/rss/article/edit/{{$item->id}}"><span class="btn btn-primary">編集</span></a>
					<a class="confirm" href="/rss/article/del/{{$item->id}}"><span class="btn btn-danger">削除</span></a>
				</td>
				@endif
			<tr>
			@endforeach
			@else
			<tr><td colspan="6">記事がありません</td></tr>
			@endif
		</table>
		{{$articles->links()}}
	<!--/.panel-body--></div>
<!--/.panel--></div>
@stop

@section('js')
@stop