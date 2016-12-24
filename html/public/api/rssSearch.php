<?php
$url = $_GET['url'];
$html = file_get_contents($url);

$ptn = '<link rel="alternate".*?href="(.*?)"';
$ptn2 = 'http://(.*)/';
$ptn3 = '<title>(.*?)</title>';

preg_match_all("@$ptn@s",$html,$m);
preg_match("@$ptn2@",$url,$m2);
preg_match("@$ptn3@",$html,$m3);

header("Content-type: text/plain; charset=UTF-8");
$r['rssurl']=$m[1][0];
$r['refm']=$m2[1];
$r['sitename']=$m3[1];
echo json_encode($r);
exit;

//echo $m[1][0];
//var_dump($m);