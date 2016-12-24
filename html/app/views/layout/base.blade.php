<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=Edge,chrome=1">
	<title>{{ $ASP_NAME }}</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	{{ HTML::style('packages/bootstrap/css/bootstrap.min.css', array('media' => 'screen')) }}
	<link href="//code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" rel="stylesheet" />
	<link href="css/colorbox.css" rel="stylesheet">
	{{ HTML::style('css/style.css', array('media' => 'screen')) }}
	<style>
	</style>
</head>

<body>
<div id="wrap">
<div class="container">
<div class="row">
<div class="logo clr10 alert alert-success">ヘッダー領域<!--<img src="http://placehold.jp/1000x50.png">--></div>
<div class="content col-lg-12">
@yield('side')
@yield('content')
</div>

<!-- /.row --></div>
<!-- /.container --></div>
<!-- /#wrap --></div>

<!-- jQuery (BootstrapのJavaScriptプラグインのために必要) -->
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
{{ HTML::script('packages/bootstrap/js/bootstrap.min.js')}}
{{ HTML::script('js/jquery.colorbox-min.js') }}
{{ HTML::script('js/footerFix.js') }}
<script type="text/javascript">
$(function() {
	$(".inline").colorbox({inline:true, width:"50%"});
	//$(".inline80").colorbox({inline:true, width:"80%"});

	//$("#clientList").tablesorter();
	//$("#excelList").tablesorter();

	setTimeout(function(){
		$(".msg").hide();
	},1500);
});
</script>

<div id="footer">
<div class="container">
copyright &copy; 2014- mire corporation
<!-- /.container --></div>
<!-- /#footer --></div>

</body>
</html>