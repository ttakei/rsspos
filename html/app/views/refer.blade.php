@extends('layout.admin')

@section('side')
	@parent
@stop

@section('content')
<div class="panel panel-primary">
	<div class="panel-heading">ログ集計</div>
	<div class="panel-body">
		<div class="text-right"><a href="/rss/referall/{{$cfg['id']}}">ログ一覧</a></div>
		<table class="tablesorter table table-striped" width="100%" border="0">
			<tr>
				<td>リファ</td>
				<td width=80>アクセス数</td>
			</tr>
				@if(count($refer) > 0)
				@foreach($refer as $ref)
			<tr>
				<td>{{$ref->refer}}</a></td>
				<td>{{$ref->cnt}}</td>
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