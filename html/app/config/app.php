<?php

return array(

	'debug' => array_key_exists('REMOTE_ADDR', $_SERVER)?in_array($_SERVER['REMOTE_ADDR'], array('180.214.224.5','124.97.12.102')):false,

	'movService'=>[
		'movdefault'=>'該当なし',
		'xvideo'=>'xvideo',
		'fc2'=>'FC2',
		'fc2ja'=>'FC2JA',
		'xhamster'=>'xhamster',
		'redtube'=>'redtube',
		'erovideonet'=>'erovideonet',
		'pornhub'=>'pornhub',
		'pipii'=>'pipii',
		'javynow'=>'javynow',
		'VJAV'=>'VJAV',
		'ShareVideos'=>'ShareVideos',
		'TokyoTube'=>'TokyoTube',
		'Tube8'=>'Tube8',
		'spankbang'=>'spankbang',
		'youporn'=>'youporn',
		'txxx'=>'txxx'
	],
	'post_status' => ['publish'=>'公開','draft'=>'下書き'],

	'category' =>
	['総合', '人妻', '熟女', '女子高生', 'ロリ', 'レイプ', '巨乳', '爆乳',
	'マニアック', 'お姉さん', 'ギャル', 'レズ', '素人', 'マッサージ', 'コスプレ',
	'痴漢', '痴女', 'AV女優','お宝', '胸チラ', 'パンチラ', '芸能人', '無修正'],
	'sitetype' => ['メインサイト','サテライトサイト'],
	'blogType' => ['wp'=>'WordPress','fc2'=>'FC2','ld'=>'ライブドア'],
	'defTpl' => [
		'xvideo'=>'<iframe src="http://flashservice.xvideos.com/embedframe/#movid#" frameborder=0 width=510 height=400 scrolling=no></iframe>',

		'fc2'=>'<script src="http://static.fc2.com/video/js/outerplayer.min.js" url="http://video.fc2.com/a/content/#movid#/" tk="TmpBeE5qRTNNamc9" tl="【人妻動画】#title#" sj="15" d="3605" w="700" h="444"  charset="UTF-8"></script>',

		'fc2ja'=>'<script src="http://static.fc2.com/video/js/outerplayer.min.js" url="http://video.fc2.com/a/content/#movid#/" tk="TmpBeE5qRTNNamc9" tl="【人妻動画】#title#" sj="15" d="3605" w="700" h="444"  charset="UTF-8"></script>',

		'xhamster'=>'<iframe width="510" height="400" src="http://xhamster.com/xembed.php?video=#movid#" frameborder="0" scrolling="no"></iframe>',

		'redtube'=>'<iframe src="http://embed.redtube.com/?id=#movid#&bgcolor=000000" frameborder="0" width="510" height="400" scrolling="no"></iframe>',

		'erovideonet'=>'<script type="text/javascript" src="http://ero-video.net/js/embed_evplayer.js"></script><script type="text/javascript">embedevplayer("mcd=#movid#", 450, 337);</script>

#url#',

		'pornhub'=>'<iframe src="http://www.pornhub.com/embed/#movid#" frameborder="0" width="608" height="468" scrolling="no"></iframe>',

		'pipii'=>'<iframe src="http://www.pipii.tv/player?id=#movid#&embed=1&width=684&height=385" width="684" height="410" class="pipii_player_iframe" title="#title#" style="vertical-align:bottom;"></iframe><script type="text/javascript" src="http://www.pipii.tv/js/player_embed.js"></script>',

		'javynow'=>'<iframe src="http://javynow.com/player.php?id=#movid#&n=1&s=1$h=480" id="player30419" style="border:none;" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>',
		'VJAV'=>'<iframe width="1280" height="745" src="http://jp.vjav.com/embed/#movid#" frameborder="0" allowfullscreen webkitallowfullscreen mozallowfullscreen oallowfullscreen msallowfullscreen></iframe>',
		'ShareVideos'=>'<iframe src="http://embed.share-videos.se/auto/embed/#movid#" frameborder=0 width=100% height=480 scrolling=no allowFullScreen></iframe>',
		'TokyoTube'=>'<script type="text/javascript" src="http://www.tokyo-tube.com/embedcode/v#movid#/u/player/w452/h361"></script>',
		'Tube8'=>'<iframe src="https://www.tube8.com/embed/#movid#" frameborder="0" height="481" width="608" scrolling="no" allowfullscreen="true" webkitallowfullscreen="true" mozallowfullscreen="true" name="t8_embed_video"></iframe>',
		'spankbang'=>'<iframe width="560" height="315" src="http://spankbang.com/#movid#/embed/" frameborder="0" scrolling="no" allowfullscreen></iframe>',
		'youporn'=>'<iframe src="http://www.youporn.com/embed/#movid#" frameborder=0 height="481" width="608" scrolling=no name="yp_embed_video"></iframe>',
		'txxx'=>'<iframe width="1280" height="745" src="http://www.txxx.com/embed/#movid#" frameborder="0" allowfullscreen webkitallowfullscreen mozallowfullscreen oallowfullscreen msallowfullscreen></iframe>'
	],

	'url' => 'http://localhost',

	'timezone' => 'Asia/Tokyo',
	'locale' => 'ja',
	'key' => 'bYYT8UZz4AG9a0iEzO9XBXIvWndH83Wv',
	'providers' => array(

		'Illuminate\Foundation\Providers\ArtisanServiceProvider',
		'Illuminate\Auth\AuthServiceProvider',
		'Illuminate\Cache\CacheServiceProvider',
		'Illuminate\Session\CommandsServiceProvider',
		'Illuminate\Foundation\Providers\ConsoleSupportServiceProvider',
		'Illuminate\Routing\ControllerServiceProvider',
		'Illuminate\Cookie\CookieServiceProvider',
		'Illuminate\Database\DatabaseServiceProvider',
		'Illuminate\Encryption\EncryptionServiceProvider',
		'Illuminate\Filesystem\FilesystemServiceProvider',
		'Illuminate\Hashing\HashServiceProvider',
		'Illuminate\Html\HtmlServiceProvider',
		'Illuminate\Log\LogServiceProvider',
		'Illuminate\Mail\MailServiceProvider',
		'Illuminate\Database\MigrationServiceProvider',
		'Illuminate\Pagination\PaginationServiceProvider',
		'Illuminate\Queue\QueueServiceProvider',
		'Illuminate\Redis\RedisServiceProvider',
		'Illuminate\Remote\RemoteServiceProvider',
		'Illuminate\Auth\Reminders\ReminderServiceProvider',
		'Illuminate\Database\SeedServiceProvider',
		'Illuminate\Session\SessionServiceProvider',
		'Illuminate\Translation\TranslationServiceProvider',
		'Illuminate\Validation\ValidationServiceProvider',
		'Illuminate\View\ViewServiceProvider',
		'Illuminate\Workbench\WorkbenchServiceProvider',
'Barryvdh\Debugbar\ServiceProvider',
	),
	'manifest' => storage_path().'/meta',
	'aliases' => array(

		'App'             => 'Illuminate\Support\Facades\App',
		'Artisan'         => 'Illuminate\Support\Facades\Artisan',
		'Auth'            => 'Illuminate\Support\Facades\Auth',
		'Blade'           => 'Illuminate\Support\Facades\Blade',
		'Cache'           => 'Illuminate\Support\Facades\Cache',
		'ClassLoader'     => 'Illuminate\Support\ClassLoader',
		'Config'          => 'Illuminate\Support\Facades\Config',
		'Controller'      => 'Illuminate\Routing\Controller',
		'Cookie'          => 'Illuminate\Support\Facades\Cookie',
		'Crypt'           => 'Illuminate\Support\Facades\Crypt',
		'DB'              => 'Illuminate\Support\Facades\DB',
		'Eloquent'        => 'Illuminate\Database\Eloquent\Model',
		'Event'           => 'Illuminate\Support\Facades\Event',
		'File'            => 'Illuminate\Support\Facades\File',
		'Form'            => 'Illuminate\Support\Facades\Form',
		'Hash'            => 'Illuminate\Support\Facades\Hash',
		'HTML'            => 'Illuminate\Support\Facades\HTML',
		'Input'           => 'Illuminate\Support\Facades\Input',
		'Lang'            => 'Illuminate\Support\Facades\Lang',
		'Log'             => 'Illuminate\Support\Facades\Log',
		'Mail'            => 'Illuminate\Support\Facades\Mail',
		'Paginator'       => 'Illuminate\Support\Facades\Paginator',
		'Password'        => 'Illuminate\Support\Facades\Password',
		'Queue'           => 'Illuminate\Support\Facades\Queue',
		'Redirect'        => 'Illuminate\Support\Facades\Redirect',
		'Redis'           => 'Illuminate\Support\Facades\Redis',
		'Request'         => 'Illuminate\Support\Facades\Request',
		'Response'        => 'Illuminate\Support\Facades\Response',
		'Route'           => 'Illuminate\Support\Facades\Route',
		'Schema'          => 'Illuminate\Support\Facades\Schema',
		'Seeder'          => 'Illuminate\Database\Seeder',
		'Session'         => 'Illuminate\Support\Facades\Session',
		'SSH'             => 'Illuminate\Support\Facades\SSH',
		'Str'             => 'Illuminate\Support\Str',
		'URL'             => 'Illuminate\Support\Facades\URL',
		'Validator'       => 'Illuminate\Support\Facades\Validator',
		'View'            => 'Illuminate\Support\Facades\View',
'Debugbar' => 'Barryvdh\Debugbar\Facade',

	),

);
