<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=Edge,chrome=1">
	<title>{{ $cfg['ASP_NAME'] }}</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	{{ HTML::style('packages/bootstrap/css/bootstrap.min.css', array('media' => 'screen')) }}
	<link href="//code.jquery.com/ui/1.9.1/themes/smoothness/jquery-ui.css" rel="stylesheet" />
	{{ HTML::style('css/spectrum.css', array('media' => 'screen')) }}
	{{ HTML::style('css/style.css', array('media' => 'screen')) }}
	{{ HTML::style('css/tablesort.css', array('media' => 'screen')) }}
	<link href="/css/colorbox.css" rel="stylesheet">
	<style>
	</style>
</head>

<body>
<div id="wrap">
<nav class="navbar navbar-default" role="navigation">
	<div class="navbar-header">
		<a class="navbar-brand" href="/rss">{{$cfg['ASP_NAME']}}</a>
	</div>
	<ul class="nav navbar-nav">
		<p class="navbar-text">{{$cfg['nickname']}}ログイン中</p>
		<li><a href="{{route('mypage')}}">マイページ</a></li>
		<li><a href="/rss/site">サイト管理</a></li>
		<li class="disabled"><a href="/cron/rssGet.php" target="_blank">RSS手動取得(旧)</a></li>
		<li><a href="/cron/rssGet" target="_blank">RSS手動取得(新)</a></li>
		<li><a href="/cron/rssPost" target="_blank">手動投稿(新)</a></li>
		<li><a href="{{route('replace')}}">置換設定</a></li>
		<li><a href="{{route('actress')}}">女優名設定</a></li>
		<li><a href="{{route('logout')}}">ログアウト</a></li>
	</ul>
</nav>

<div class="container">
<div class="row">
<div class="content col-lg-12">
<div class="side col-lg-3">
@section('side')
	<div class="btn btn-warning clr10"><a href="/rss">管理画面TOP</a></div>

	<div class="panel panel-default panel-primary info clr10">
		<div class="panel-heading">現在のサイト</div>
		{{Form::open(array('url' => '/rss/site', 'method' => 'post'))}}
			{{$cfg['HTMLselectsite']}}
		{{Form::close()}}
	</div>

	@if(Session::has('acc'))
	<div class="panel panel-default panel-primary info clr10 {{$show or 'hide'}}">
		<div class="panel-heading">サイトメニュー</div>
		<ul class="list-group">
			<li class="list-group-item"><a href="{{route('blog')}}" class="">ブログリスト</a></li>
			<li class="list-group-item"><a href="{{route('parts')}}" class="">パーツリスト</a></li>
			<li class="list-group-item"><a href="{{route('article')}}" class="">記事リスト</a></li>
			<li class="list-group-item"><a href="{{route('refloop')}}" class="">リファリスト</a></li>
			<li class="list-group-item"><a href="{{route('ngword')}}" class="">取込除外ワード</a></li>
			<li class="list-group-item"><a href="{{route('postword')}}" class="">投稿ワード</a></li>
			<li class="list-group-item"><a href="{{route('noActress')}}" class="">NON女優名設定</a></li>
		</ul>
	</div>
	@endif
@show
</div>
<div class="content col-lg-9">
@yield('content')
</div>
</div>

</div> <!-- /.row -->
</div><!-- /.container -->
</div><!-- /#wrap -->

<!-- jQuery (BootstrapのJavaScriptプラグインのために必要) -->
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
{{ HTML::script('packages/bootstrap/js/bootstrap.min.js', array('media' => 'screen')) }}
{{ HTML::script('js/jquery.colorbox-min.js', array('media' => 'screen')) }}
{{ HTML::script('js/spectrum.js', array('media' => 'screen')) }}
{{ HTML::script('js/jquery.tablesorter.min.js', array('media' => 'screen')) }}
<script type="text/javascript">
$(function() {
	$(".hide").hide();
	$(".inline").colorbox({inline:true, width:"50%"});
	$(".inline80").colorbox({inline:true, width:"80%"});

	$("#blogList").tablesorter();
	//$("#excelList").tablesorter();

	setTimeout(function(){
		$(".msg").hide();
	},1500);

	//$(".datepicker").datepicker({ dateFormat: "yy-mm-dd",numberOfMonths: 3});
	//$(".datepicker").datepicker($.datepicker.regional[ "ja" ]);

	$("a.confirm").click(function(e){
		e.preventDefault();
		thisHref	= $(this).attr('href');
		if(confirm('削除して良いですか？')) {
			window.location = thisHref;
		}
	})

});
</script>

@yield('js')

{{--
<div id="footer">
<div class="container">
copyright &copy; 2014- mire
</div><!-- /.container -->
</div> <!-- /#footer -->
--}}
</body>
</html>