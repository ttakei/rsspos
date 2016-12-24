@extends('layout.admin')

@section('side')
	@parent
@stop

@section('content')
<div class="panel panel-primary">
	<div class="panel-heading">ログ一覧</div>
	<div class="panel-body">
		<div class="text-right"><a href="/rss/refer/{{$cfg['id']}}">ログ集計</a></div>
		<table class="tablesorter table table-striped" width="100%" border="0">
			<tr>
				<td width=150>日時</td>
				<td>リファ</td>
				<td width=80>IP</td>
			</tr>
				@if(count($refer) > 0)
				@foreach($refer as $ref)
			<tr>
				<td>{{date('Y-m-d H:i:s',$ref->utime)}}</td>
				<td>{{$ref->refer}}</a></td>
				<td>{{$ref->ip}}</td>
			<tr>
				@endforeach
				@else
			<tr><td colspan="6">ログがありません</td></tr>
				@endif
		</table>
{{ $refer->links() }}
	<!--/.panel-body--></div>
<!--/.panel--></div>
@stop