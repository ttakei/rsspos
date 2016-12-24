<?php

require './simple_html_dom.php';

// tokyotube

//$html = file_get_html('http://www.tokyo-tube.com/videos?type=public&o=lg&page=10');
// 最新順
$html = file_get_html('http://www.tokyo-tube.com/videos?type=public&o=mr&page=10');

$contentsList = array();
foreach ($html->find('.postBox_wrap .postBox a') as $node)
{
	//var_dump($node->href);
	//echo '<hr>';
  $val1 = null;
	$val2 = null;
	$val3 = null;
	$id = split('/video', $node->href);
	$id = split('/', $id[1]);
	$val1 = $id[1];
	foreach ($node->find('img') as $node2)
	{
		$val2 = $node2->alt;
		$val3 = $node2->src;
		break;
	}
	$val2 = $node->find('p.tt',0)->plaintext;
	if ($val1 && $val3) {
		array_push($contentsList, array("id" => $val1, "type" => 3, "title" => $val2, "image" => $val3));
	}
	echo "val1:$val1 / val2:$val2 / val3:$val3<br>";
}

$html->clear();

var_dump($contentsList);
echo '<hr><hr>';


$contentsList = array();
// tokyo porn tube
$html = file_get_html('http://www.tokyo-porn-tube.com/videos?type=public&o=mr&page=8');
foreach ($html->find('.postBox_wrap .postBox a') as $node)
{
	$val1 = null;
	$val2 = null;
	$val3 = null;
	$id = split('/video', $node->href);
	$id = split('/', $id[1]);
	$val1 = $id[1];
	foreach ($node->find('img') as $node2)
	{
		$val2 = $node2->alt;
		$val3 = $node2->src;
		break;
	}
	$val2 = $node->find('p.tt',0)->plaintext;
	echo "val1:$val1 / val2:$val2 / val3:$val3<br>";
	if ($val1 && $val3) {
		array_push($contentsList, array("id" => $val1, "type" => 5, "title" => $val2, "image" => $val3));
	}
}
$html->clear();

var_dump($contentsList);
echo '<hr><hr>';
