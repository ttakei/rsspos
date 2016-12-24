<?php

// 2015/3/18 石上さんから依頼
// 名前、投稿日、内容などをデータベースに保存して、リスト表示
// - １ページあたり１３件しか表示されないので、DB保存が必要
// - 名前でフィルタリングする機能も実装する

/*
$ch   = curl_init();
$options = array(
  CURLOPT_URL => 'http://xirasaya.com/',
  CURLOPT_HEADER => false,
  CURLOPT_NOBODY => false,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_USERAGENT => 'User-Agent: DoCoMo/2.0 P903i(c100;TB;W24H12)',
);
curl_setopt_array($ch, $options);
$contents = curl_exec($ch);
print $contents;
*/

$options = array(
  'http' => array(
    'method' => 'GET',
    'header' => 'User-Agent: Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1500.63 Safari/537.36',
  ),
);
$context = stream_context_create($options);
$url = "https://www.facebook.com/groups/1542704399344019/?ref=bookmarks";

$_html = file_get_contents($url,false,$context);

echo $_html;