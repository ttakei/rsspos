<?php

return array(

	//'debug' => array_key_exists('REMOTE_ADDR', $_SERVER)?in_array($_SERVER['REMOTE_ADDR'], array('124.101.195.11','180.214.224.5')):false,
	'debug' => true,

	'url' => 'http://localhost',

	'timezone' => 'Asia/Tokyo',

	'locale' => 'ja',

	'movtag'=>[
		'xvideo'=>'<iframe src="http://flashservice.xvideos.com/embedframe/#movid#" frameborder=0 width=360 height=240 scrolling=no></iframe>',
		'fc2'=>'<script src="http://static.fc2.com/video/js/outerplayer.min.js" url="http://video.fc2.com/a/content/#movid#/" tk="TmpBeE5qRTNNamc9" tl="#title#" sj="15" d="3605" w="360" h="240"  charset="UTF-8"></script>',
		'fc2ja'=>'<script src="http://static.fc2.com/video/js/outerplayer.min.js" url="http://video.fc2.com/ja/a/content/#movid#/" tk="TmpBeE5qRTNNamc9" tl="アダルト動画" sj="15" d="3605" w="360" h="240"  charset="UTF-8"></script>',
		'xhamster'=>'<iframe width="360" height="240" src="http://xhamster.com/xembed.php?video=#movid#" frameborder="0" scrolling="no"></iframe>',
		'redtube'=>'<iframe src="http://embed.redtube.com/?id=#movid#&bgcolor=000000" frameborder="0" width="360" height="240" scrolling="no"></iframe>',
		'erovideonet'=>'<script type="text/javascript" src="http://ero-video.net/js/embed_evplayer.js"></script><script type="text/javascript">embedevplayer("mcd=#movid#", 360, 320);</script>',
		'pornhub'=>'<iframe src="http://www.pornhub.com/embed/#movid#" frameborder="0" width="360" height="240" scrolling="no"></iframe>',
		'pipii'=>'<iframe src="http://www.pipii.tv/player?id=#movid#&embed=1&width=360&height=240" width="360" height="240" class="pipii_player_iframe" title="#title#" style="vertical-align:bottom;"></iframe><script type="text/javascript" src="http://www.pipii.tv/js/player_embed.js"></script>'
	],

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
