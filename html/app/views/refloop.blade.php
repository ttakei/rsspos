@extends('layout.admin')

@section('side')
	@parent
@stop

@section('content')
<div class="panel panel-primary">
	<div class="panel-heading">ブログ一覧</div>
	<div class="panel-body">
		<div class="text-left clr10">
		<div class="col-sm-3">
	  <div class="btn btn-default"><a href="{{route('blog.edit')}}">ブログ追加</a></div>
	  </div>
	  <div class="col-sm-9 text-right">
	  pv : {{$cnt['pv']}}<br>
	  in : {{$cnt['in']}}<br>
	  out : {{$cnt['out']}}
	  </div>
	  </div>
		<table id="blogList" class="tablesorter table table-striped" width="100%" border="0">
			<tr>
				<td>ID</td>
				<td>サイト名称</td>
				<td>PV</td>
				<td width=100>操作</td>
			</tr>
				@if(count($anly) > 0)
				@foreach($anly as $ak=>$av)
				<?php
					$id = '-';
					$siteurl = $av->url;
					$name = ($av->url=='')?'ブックマーク':$av->url;
					$in = '1';
					$out = '1';
					foreach($blogs as $blog){
						if($av->url==$blog->siteurl){
							$id = $blog->id;
							$siteurl = $blog->siteurl;
							$name = $blog->name;
							$in = $blog->in;
							$out = $blog->out;
							break;
						}
					}
				?>
			<tr
			@if($id=='-')
			 class="warning"
			@endif
			>
				<td>
				{{$id}}
				</td>
				<td><a href="{{$siteurl}}" target="_blank">{{$name}}</a></td>
				<td>
				{{$av->cnt}}
				</td>
				<td>
					@if($id!='-')
					<a href="/rss/blog/edit/{{$id}}"><span class="glyphicon glyphicon-cog"></span></a>
					@else
					<a href="/rss/blog/edit/{{ str_replace("http://",'',$av->url)}}"><span class="glyphicon glyphicon-cog"></span></a>
					@endif
					<a href="/rss/refer/{{ str_replace("http://",'',$av->url)}}"><span class="glyphicon glyphicon-signal"></span></a>
					@if($id!='-')
					<a class="confirm" href="/rss/blog/del/{{$id}}"><span class="glyphicon glyphicon-trash"></span></a>
					@endif
				</td>
			<tr>
				@endforeach
				@else
			<tr><td colspan="4">管理サイトがありません</td></tr>
				@endif
		</table>
	<!--/.panel-body--></div>
<!--/.panel--></div>
@stop

@section('js')
@stop