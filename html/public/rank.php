$(function(){
	$("a").bind("click",function(){
		var url = $(this).attr('href');
		if( url.indexOf(location.hostname)<0){
			var text = $(this).text();
			$.ajax({
			type:"POST",
			url:"http://rsspos.net/count.php?id=<?php echo $_GET['id']?>",
			data:{url:url,text:text}
			}).done(function(msg){
			});
		}
	});
});
var ref = document.referrer;
document.write('<script type="text/javascript" src="http://rsspos.net/js.php?id=<?php echo $_GET['id']?>&r='+ref+'"></script>');
