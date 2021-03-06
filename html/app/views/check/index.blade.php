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
				<td>取得元 | 取得時間 | 予約時間 | 投稿時間</td>
			</tr>
			@if(count($articles) > 0)
			@foreach($articles as $item)
			<tr>
				<td>{{$item->id}}</td>
				<td>@if($item->imgurl!='')<img src="{{$item->imgurl}}" width=200>@endif</td>
				<td class="{{($item->movid=='')?'danger':'';}}">
					@if(Session::get('role')=='admin')
						<a href="{{$item->url}}" target="_blank">{{$item->name}}</a> | 
					@endif
					<p class="label label-success">{{$item->movSite}}{{($item->movlink!='')?' (link)':''}}</p><br>
					{{$item->created_at}} | <span style="{{($item->researved_at!='0000-00-00 00:00:00')?'background-color:#FBB':''}}">{{$item->researved_at}}</span> | <span style="{{($item->posted_at!='0000-00-00 00:00:00')?'background-color:#BBB':''}}">{{$item->posted_at}}</span><br>
					置換前：{{$item->title_org}}<br>置換後：{{$item->title}}<br>リライト：<span class="label label-success">{{$item->title_rewrite}}</span><br>
					description：{{$item->seo_desc}}</td>
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