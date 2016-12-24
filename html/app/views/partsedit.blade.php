@extends('layout.admin')

@section('side')
@parent
<div class="panel panel-default panel-primary info clr10">
	<div class="panel-heading">プレビュー</div>
	<div class="panel-body">
		<script src="http://{{$_SERVER['HTTP_HOST']}}/api.php?id={{Session::get('acc')}}&limit=1&p={{$parts['id']}}"></script>
	</div>
</div>
@stop

@section('content')
<div class="panel panel-primary">
	<div class="panel-heading">パーツ設定</div>
	<div class="panel-body">
		{{Form::open(array('url'=>'/rss/parts/edit'))}}
		{{Form::hidden('id',$parts['id'])}}
		{{Form::textField('name','パーツ名称',$parts['name'])}}
		{{Form::textareaField('tpl','内容',$parts['tpl'])}}
		<div class="alert alert-info">広告タグ</div>
		{{Form::textField('adline1','広告１行数',$parts['adline1'])}}
		{{Form::textareaField('adtpl1','広告１内容',$parts['adtpl1'])}}
		{{Form::textField('adline2','広告２行数',$parts['adline2'])}}
		{{Form::textareaField('adtpl2','広告２内容',$parts['adtpl2'])}}
		{{Form::textField('adline3','広告３行数',$parts['adline3'])}}
		{{Form::textareaField('adtpl3','広告３内容',$parts['adtpl3'])}}

		{{Form::submit('保存',array('class'=>'btn btn-primary'))}}
		{{Form::close()}}
		<br>
		<div class="alert alert-info clr10">貼り付けタグ</div>
		<div class="well">
<div class="label label-success">offset</div><br>
 m=id以外のみ利用可能です。<br>
 デフォルトでoffset=1 です。（最新の記事を利用します）<br>
 offset=2とすると新しいものから２番めの記事が表示されます。<br>

<div class="label label-primary">パーツ{{$parts['id']}}を利用し最大50表示</div><br>
&lt;script src="http://{{$_SERVER['HTTP_HOST']}}/api.php?id={{Session::get('acc')}}&limit=50&p={{$parts['id']}}">&lt;/script><br>
<div class="label label-primary">パーツ{{$parts['id']}}を利用し10～20位を表示</div><br>
&lt;script src="http://{{$_SERVER['HTTP_HOST']}}/api.php?id={{Session::get('acc')}}&start=10&limit=10&p={{$parts['id']}}">&lt;/script><br>
<br>
<div class="label label-primary">パーツ{{$parts['id']}}を利用しアウトレートの低い順に最大10表示</div><br>
&lt;script src="http://{{$_SERVER['HTTP_HOST']}}/api.php?id={{Session::get('acc')}}&limit=10&p={{$parts['id']}}&m=outrate">&lt;/script><br>
<br>
<div class="label label-primary">ブログID=5をパーツ{{$parts['id']}}を利用し最大5表示</div><br>
&lt;script src="http://{{$_SERVER['HTTP_HOST']}}/api.php?id={{Session::get('acc')}}&limit=5&p={{$parts['id']}}&m=id&selid=5">&lt;/script><br>
<div class="label label-primary">ブログID=5,7,8をパーツ{{$parts['id']}}を利用し最大5表示</div><br>
&lt;script src="http://{{$_SERVER['HTTP_HOST']}}/api.php?id={{Session::get('acc')}}&limit=5&p={{$parts['id']}}&m=mid&selid=5,7,8">&lt;/script>
		</div>

		<div class="alert alert-info clr10">独自タグ</div>
		<table class="table table-striped">
		<tr>
			<td>#name#</td>
			<td>ブログ名</td>
		</tr>
		<tr>
			<td>#title#</td>
			<td>記事タイトル or 強制タイトル（強制タイトルがからでない場合）</td>
		</tr>
		<tr>
			<td>#title:文字数#</td>
			<td>記事タイトルの指定文字数</td>
		</tr>
		<tr>
			<td>#blogurl#</td>
			<td>ブログURL</td>
		</tr>
		<tr>
			<td>#url#</td>
			<td>記事URL or 強制URL(強制URLが空でない場合）</td>
		</tr>
		<tr>
			<td>#imgurl#</td>
			<td>画像URL or 強制画像(強制画像が空でない場合）</td>
		</tr>
		<tr>
			<td>#no#</td>
			<td>１からの連番</td>
		</tr>
		<tr>
			<td>#no0#</td>
			<td>0からの連番</td>
		</tr>
		<tr>
			<td>#in#</td>
			<td>INアクセス数</td>
		</tr>
		<tr>
			<td>#out#</td>
			<td>OUTアクセス数</td>
		</tr>
		<tr><td colspan=2></td></tr>
		<tr>
			<td>ヘッダ<br>
			#loop<br>
		～<br>
		#endloop<br>
		フッタ</td>
			<td>ループ内を指定数分ループ表示（テーブル、CSS等にご利用下さい)</td>
		</tr>
	<!--/.panel-body--></div>
<!--/.panel--></div>
@stop