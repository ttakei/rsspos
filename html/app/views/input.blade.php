@extends('layout.admin')

@section('side')

	@parent

	<div class="panel panel-warning info clr10">
		<div class="panel-heading">広告</div>
		<div class="panel-body">
		</div>
	</div>
@stop

@section('content')
<style>
td {
	font-size:12px !important;
}
.week6{
	background-color: #cff;
}
.week0{
	background-color: #fcc;
}
</style>
<div class="content col-lg-10">
	<div class="alert alert-info">スケジュール登録</div>
	<div class="row">
		<div class="nowYm col-lg-3"><div class="alert alert-success">{{ $cfg['Y'] }}年{{$cfg['m'] }}月</div></div>
		<div class="shift col-lg-9">
			<div class="prevMonth col-lg-2 text-center"><a href="{{route('calinput')}}/{{$cfg['prev']}}">前月</a></div>
			<div class="selectMonth col-lg-8 text-center">年月指定</div>
			<div class="nextMonth col-lg-2 text-center"><a href="{{route('calinput')}}/{{$cfg['next']}}">次月</a></div>
		</div>
	</div>

	<form method="POST" action="/cal/multi/input">
	<input type="checkbox" id="all">すべてチェック
	<button type="submit" class="btn btn-success" name='add' value='1' id="multiadd">まとめて登録</button>	
	<button type="submit" class="btn btn-danger" name='del' value='1' id="multidel">まとめて削除</button>	
	<div class="clr10"></div>
	<table class="table table-bordered table-condensed" width="100%" border="0">
		<tr>
			<td width="20"></td>
			<td width="40" class="text-center">日付</td>
			<td width="40" class="text-center">曜日</td>
			<td>スケジュール</td>
		</tr>
		{{$cfg['html']}}
	</table>
	</form>
</div>

<div style='display:none'>
	<div id='inline_content' style='padding:10px; background:#fff;'>
		{{ Form::open(array('url' => '/cal/input', 'method' => 'post'))}}
		<input type="hidden" name="Ym" value="" id="Ym">
		<div class="alert alert-info selected_date">日付が入る</div>
		<label>アイコン</label>
		{{$cfg['iconHTML']}}

		<div class="clr10"></div>

		<label>タイトル</label>
		<input type="text" name="title" class="form-control" id="title">

		<div class="clr10"></div>

		<label>コメント</label>
		<textarea name='comment' id='comment' class="form-control"></textarea>

		<div class="clr10"></div>

		<input type='submit' class="btn btn-danger" value="登録">
		{{ Form::close()}}
	</div>
</div>
@stop

@section('js')
<script>
$(function(){
	$(".inputArea").click(function(){
		$("input[name='icon']").attr('checked',false);
		var dateY = $(this).data('y');
		var datem = $(this).data('m');
		var dated = $(this).data('d');
		var icon = $(this).data('icon');
		//console.log(icon);
		var title = $(this).data('title');
		var comment = $(this).data('comment');
		$(".selected_date").html(dateY+'年'+datem+'月'+dated+'日');
		$("#Ym").val(dateY+datem+dated);
		$("input[name='icon'][value='"+icon+"']").attr('checked',true);
		//var hoge = $("input[name='icon']").val();
		//if(hoge==0) $("input[name='icon'][value='0']").attr('checked',true);
		$("#title").val(title);
		$("#comment").text(comment);
	});
	$('#all').on('change', function() {
	    $('.checkall').prop('checked', this.checked);
	});
})
</script>
@stop