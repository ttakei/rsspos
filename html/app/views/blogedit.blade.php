@extends('layout.admin')

@section('content')
<div class="panel panel-primary">
	<div class="panel-heading">ブログ設定</div>
	<div class="panel-body">
		{{Form::open(array('url'=>'/rss/blog/edit'))}}
		{{Form::hidden('id',$blogs['id'])}}
		{{Form::textField('siteurl','ブログURL',$blogs['siteurl'],array('id'=>'url'))}}
		{{Form::textField('name','ブログ名称',$blogs['name'],array('id'=>'sitename'))}}
		{{Form::textField('rssurl','RSS URL',$blogs['rssurl'],array('id'=>'rssurl'))}}

		@if($_SERVER['SERVER_ADDR']=='153.120.34.241')
		<div class="panel panel-success">
			<div class="panel-heading">rssrank.net用仕様</div>
			<div class="panel-body">
			{{Form::textField('domTitle','スクレイピング・タイトル',$blogs['domTitle'])}}
			{{Form::textField('domBody','スクレイピング・本文(本文中の画像をDL＆アップロードします)',$blogs['domBody'])}}
			{{Form::textField('filterBody2','削除エリア(正規表現)',$blogs['filterBody2'])}}
			{{Form::textareaField('filterBody1','削除エリア（HTMLベタ)',$blogs['filterBody1'])}}
			</div>
		</div>
		@endif

		{{Form::submit('保存',array('class'=>'btn btn-primary'))}}
		{{Form::close()}}
	<!--/.panel-body--></div>
<!--/.panel--></div>
@stop

@section('js')
<script>
	function get_rssurl(e){
		var url = $(this).val();
		$.ajax({
		type: "GET",
		url: '/api/rssSearch.php',
		data:{url:url}
	})
	.done(function(d) {
		var parseAry = JSON.parse(d);
		var rssurl = parseAry['rssurl'];
		var refm = parseAry['refm'];
		var sitename = parseAry['sitename'];
		$('#rssurl').val(rssurl);
		$('#refermatch').val(refm);
		$('#sitename').val(sitename);
	})
	.fail(function(d) {
	// ...
	});
	};

	$("#url").bind("blur", get_rssurl);
	$("#url").bind("change", get_rssurl);
</script>
@stop