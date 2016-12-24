@extends('layout.admin')

@section('side')
	@parent
	
	<div class="panel panel-warning info clr10">
		<div class="panel-heading">新着情報</div>
		<div class="panel-body">
			<dl>
				@if(count($news) > 0)
				@foreach($news as $v)
				<dt>{{date('Y-m-d',strtotime($v->created))}}</dt>
				<dd>{{ $v->title }}</dd>
				@endforeach
				@else お知らせはありません。
				@endif
			</dl>
		</div>
	</div>
@stop

@section('content')
<div class="panel panel-info">
	<div class="panel-heading">お知らせ</div>
	<div class="panel-body">
		<dl>
			@if(count($news) > 0)
			@foreach($news as $v)
			<dt>{{ $v->title }}({{date('Y-m-d',strtotime($v->created))}})</dt>
			<dd>{{ $v->desc }}</dd>
			@endforeach
			@else お知らせはありません。
			@endif
		</dl>
	</div>
</div>
@stop