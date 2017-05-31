<?php

class CronController2 extends BaseController {

	const MAX_TIME_LIMIT = 600;  // 処理全体の最大処理時間(秒)
	const MAX_MEMORY_MBYTE = '250M';
	const CURL_TIMEOUT = 10;
	const MULTI_CURL_TIMEOUT = 10;
	const MULTI_CURL_MAX_FAIL_CNT = 1;
	const MULTI_CURL_MAX_DOMAIN_PARA = 1;  // ドメイン単位の最大並列数
	const MULTI_CURL_MAX_PARA = 50;  // リクエスト最大並列数
	const MAX_NUM_ARTICLE_PER_RSS = 50;  //1つのrssでfetchする最大記事件数
	
	// 記事データ抽出正規表現
	private $regex_imgurl = '/<img[^>]*src\s*=\s*["\'](.*?)["\'][^>]*>/i';
	private $regex_mov = array(
		'xvideo' => array(
			'id' => '/<iframe[^>]*src\s*=\s*["\']http[s]?:\/\/flashservice\.xvideos.com\/embedframe\/(.*?)["\'\/]/i',
			'link' => '/(http[s]?:\/\/www\.xvideos\.com\/video[0-9]*\/[^"\'& ]*|http[s]?:\/\/www\.xvideos\.com\/video[0-9]*\/)/',
		),
		'fc2' => array(
			'id' => '/<script[^>]*url\s*=\s*["\']http[s]?:\/\/video\.fc2\.com\/a\/content\/(.*?)["\'\/]/i',
			'link' => '/(http[s]?:\/\/video\.fc2\.com\/a\/content\/[^\/]*\/&tk=[^"\'& ]*|http[s]?:\/\/video\.fc2\.com\/a\/content\/[^"\'& ]*)/',
		),
		'fc2ja' => array(
			'id' => '/<script[^>]*url\s*=\s*["\']http[s]?:\/\/video\.fc2\.com\/ja\/a\/content\/(.*?)["\'\/]/i',
			'link' => '/(http[s]?:\/\/video\.fc2\.com\/ja\/a\/content\/[^\/]*\/&tk=[^"\'& ]*|http[s]?:\/\/video\.fc2\.com\/ja\/a\/content\/[^\/]*\/)/',
		),
		'xhamster' => array(
			'id' => '/<iframe[^>]*src\s*=\s*["\']http[s]?:\/\/xhamster\.com\/xembed\.php\?video=(.*?)["\'\/&]/i',
			'link' => '/(http[s]?:\/\/xhamster\.com\/movies\/[0-9]*\/.*?\.html)/',
		),
		'redtube' => array(
			'id' => '/<iframe[^>]*src\s*=\s*["\']http[s]?:\/\/embed\.redtube\.com\/\?id=(.*?)["\'\/&]/i',
			'link' => '/(http[s]?:\/\/www\.redtube\.com\/[0-9]*)/',
		),
		'erovideonet' => array(
			'id' => '/<script[^>]*src\s*=\s*["\']http[s]?:\/\/ero-video\.net\/js\/embed_evplayer\.js["\'][^>]*><\/script><script[^>]*>\s*embedevplayer\s*\([\s"\']mcd=([^"]*)["\']/i',
			'link' => '/(http[s]?:\/\/ero-video\.net\/movie\/\?mcd=[0-9a-zA-Z]*)/',
		),
		'pornhub' => array(
			'id' => '/<iframe[^>]*src\s*=\s*["\']http[s]?:\/\/.*?\.pornhub\.com\/embed\/(.*?)["\'\/]/i',
			'link' => '/(http[s]?:\/\/.*?\.pornhub\.com\/view_video\.php\?viewkey=[0-9a-zA-Z]*)/',
		),
		'pipii' => array(
			'id' => '/<iframe[^>]*src\s*=\s*["\']http[s]?:\/\/www\.pipii\.tv\/player\?id=(.*?)["\'\/&]/i',
			'link' => '/(http[s]?:\/\/souko\.pipii\.tv\/archives\/movie\/[0-9]*\/)/',
		),
		'javynow' => array(
			'id' => '/<iframe[^>]*src\s*=\s*["\']http[s]?:\/\/javynow\.com\/player\.php\?id=(.*?)["\'\/&]/i',
			'link' => '/(http[s]?:\/\/javynow\.com\/video\.php\?id=[^&]*&[^&]*&[^ "\']*)/',
		),
		'VJAV' => array(
			'id' => '/<iframe[^>]*src\s*=\s*["\']http[s]?:\/\/jp\.vjav\.com\/embed\/(.*?)["\'\/]/i',
			'link' => '/(http[s]?:\/\/jp\.vjav\.com\/videos\/[0-9]*\/.*?\/)/',
		),
		'ShareVideos' => array(
			'id' => '/<iframe[^>]*src\s*=\s*["\']http[s]?:\/\/embed\.share-videos\.se\/auto\/embed\/(.*?)["\'\/]/i',
			'link' => '/(http[s]?:\/\/share-videos\.se\/auto\/video\/[0-9]*\?uid=[0-9]*)/',
		),
		'TokyoTube' => array(
			'id' => '/<script[^>]*src\s*=\s*["\']http[s]?:\/\/www\.tokyo-tube\.com\/embedcode\/v(.*?)["\'\/]/i',
			'link' => '/(http[s]?:\/\/www\.tokyo-tube\.com\/video\/[0-9]*\/["\'\/]*)/',
		),
		'Tube8' => array(
			'id' => '/<iframe[^>]*src\s*=\s*["\']http[s]?:\/\/www\.tube8\.com\/embed\/(.*?)["\']/i',
			'link' => '/(http[s]?:\/\/www\.tube8\.com\/[^\/]*\/[0-9]*\/|http[s]?:\/\/www\.tube8\.com\/[^\/]*\/[^\/]*\/[0-9]*\/)/',
		),
		'spankbang' => array(
			'id' => '/<iframe[^>]*src\s*=\s*["\']http[s]?:\/\/spankbang\.com\/(.*?)["\'\/]/i',
			'link' => '/(http[s]?:\/\/jp\.spankbang\.com\/syni\/video\/[^"\']*)/',
		),
		'youporn' => array(
			'id' => '/<iframe[^>]*src\s*=\s*["\']http[s]?:\/\/www\.youporn\.com\/embed\/(.*?)["\'\/]/i',
			'link' => '/(http[s]?:\/\/www\.youporn\.com\/watch\/[0-9]*\/[^\/]*\/)/',
		),
		'txxx' => array(
			'id' => '/<iframe[^>]*src\s*=\s*["\']http[s]?:\/\/www\.txxx\.com\/embed\/(.*?)["\'\/]/i',
			'link' => '/(http[s]?:\/\/jp\.txxx\.com\/videos\/[0-9]*\/[^\/]*\/)/',
		),
	);
	
	public function __construct(){
	}

	public function rssGet(){
		// 処理開始時間
		$time_start = microtime(true);
		Log::info("rssGet start");
		
		// 処理全体のタイムアウト時間、メモリ上限を設定する
		set_time_limit(self::MAX_TIME_LIMIT);
		ini_set('memory_limit', self::MAX_MEMORY_MBYTE);
		
		// DBに登録されたサイト
		// サイト取得
		if (!Input::has('site') && !Input::has('acc')) {
			$db_sites = Sites::all();
		} else {
			if (Input::has('site')) {
				$db_sites = Sites::where('id', Input::get('site'))->get();
			} else {
				$db_sites = Sites::where('acc', Input::get('acc'))->get();
			}
		}
		$sites = array();
		foreach ($db_sites as $db_site) {
			$sites[$db_site->acc] = array(
				'isactive' => $db_site->isactive,
				'actressFormat' => $db_site->actressFormat,
				'seotitle' => $db_site->seotitle,
				'seodesc' => $db_site->seodesc,
				'seokeyword' => $db_site->seokeyword,
			);
		}
		
		// DBに登録されたブログリスト
		if (!Input::has('site') && !Input::has('acc')) {
			$db_blogs = Blogs::where('deleted_at', '0000-00-00 00:00:00')->get();
		} else {
			$db_blogs = Blogs::where('deleted_at', '0000-00-00 00:00:00')->where('acc', $db_sites[0]->acc)->get();
		}
		
		// fetchするrss情報リスト
		// rssurl単位
		$rss_list = array();
		$rss_list_error = array();
		
		// fetchするブログ記事情報リスト
		// 記事url単位
		$blog_articles = array();
		$blog_articles_error = array(
			'registered' => array(),  // 既に処理済み 
			'ng' => array(),  // NG判定により無視
			'fetch' => array(),  // 記事取得失敗
			'over' => array(),  // MAX_NUM_ARTICLE_PER_RSSを超過して無視された記事
		);
		
		// DBに登録するブログ記事情報
		$db_articles = array();
		
		// DBのブログリストからrss情報リストにデータ格納
		foreach ($db_blogs as $db_blog) {
			$id = $db_blog->id;
			$rssurl = $db_blog->rssurl;
			$acc = $db_blog->acc;  // 入稿先サイト名
			
			// サイトがアクティブでない場合(rss無効の場合)はスルー
			if (!isset($sites[$acc]) || !$sites[$acc]['isactive']) {
				continue;
			}
			
			// 複数のサイトで同一のrssを登録している場合があるので
			if (!isset($rss_list[$rssurl])) {
				$rss_list[$rssurl] = array(
					'http_code' => null,
					'response' => null,
					'blog' => array(),
				);
			}
			$rss_list[$rssurl]['blog'][$id] = array(
				'acc' => $acc,
			);
		}
		
		// rss fetch
		if ($this->multiFetch(array_keys($rss_list), $rss_list, $rss_list_error) !== true) {
			echo("failed rss multifetch");
			Log::error("failed rss multifetch");
			exit;
		}
		// タイムアウトだけはエラーに入ってこず結果もセットされないので
		foreach ($rss_list as $rssurl => $rss) {
			if (!isset($rss['http_code'])) {
				$rss_list_error[] = $rssurl;
				Log::warning("timeout fetch rss $rssurl");
			}
		}
		Log::info("rss fetch done");
		
		// rss parse
		foreach ($rss_list as $rssurl => $rss) {
			if (!empty($rss_list_error[$rssurl])) {
				continue;
			}
			
			$article_cnt_per_rss = 0;
			
			$blogid_list = array_keys($rss['blog']);
			$feed = new SimplePie;
			$feed->set_raw_data($rss['response']);
			$feed->init();
			$feed_items=$feed->get_items();
			
			foreach ($feed_items as $item) {
				$article_url = $item->get_link();
				$title = html_entity_decode($item->get_title());
				$description = html_entity_decode($item->get_description());
				$content = html_entity_decode($item->get_content());
				$created_at = $item->get_date('Y-m-d H:i:s');
				
				$tag_arr = array();
				$categories = $item->get_categories();
				if (is_array($categories)) {
					foreach ($categories as $item_tag) {
						$label = $item_tag->get_label();
						if (!empty($label)) {
							$tag_arr[] = $label;
						}
					}
				}
				$tag = implode(',', $tag_arr);
				
				if ($article_cnt_per_rss > self::MAX_NUM_ARTICLE_PER_RSS) {
					// 1つのrssで処理する記事最大件数を超えた場合
					$blog_articles_error['over'][] = array(
						'url' => $article_url,
						'rssurl' => $rssurl,
					);
					Log::warning("num over so ignore $article_url at $rssurl");
					continue;
				}
				
				foreach ($blogid_list as $blogid) {
					$acc = $rss['blog'][$blogid]['acc'];
					
					// 登録済みか判定
					//$query = "SELECT id FROM article USE INDEX (blogid_url_unique) WHERE blogid = '$blogid' AND url = '$article_url'";
					$query = "SELECT id FROM article2 USE INDEX (blogid_url_unique) WHERE blogid = '$blogid' AND url = '$article_url'";
					$arr = DB::select($query);
					if (isset($arr[0])) {
						// 登録済みの場合
						$blog_articles_error['registered'][] = array(
							'blogid' => $blogid,
							'url' => $article_url,
							'rssurl' => $rssurl,
						);
						if (!Input::has('force_update')) {
							continue;
						}
					}
					
					// タイトル置換
					$title_replaced = $title;
					if (!empty($sites[$acc]['useReplaceWords'])) {
						$replace_words = ReplaceWords::all();
						foreach ($replace_words as $replace_word) {
							$from_word = $replace_word->from;
							$to_words = explode(',', $replace_word->to);
							$to_word = $to_words[rand(0, count($to_words) - 1)];
							$title_replaced = str_replace($from_word, $to_word, $title_replaced);
						}
					}
					
					// タイトル女優名付与
					// #actress#
					if (!empty($sites[$acc]['actressFormat'])) {
						$format_org = $sites[$acc]['actressFormat'];
						$format = $format_org;
						$actress_list = Actress::all();
						foreach ($actress_list as $actress) {
							if (strpos($title_replaced, $actress->name) !== false) {
								$format = str_replace('#actress#', $actress->name, $format);
								break;
							}
						}
						// 女優名がタイトルに含まれなかった場合
						if ($format === $format_org) {
							$no_actress_list = Noactress::where('sites_acc', $acc)->get();
							$rates = array();
							$rate_sum = 0;
							foreach ($no_actress_list as $no_actress) {
								$rate_sum += $no_actress->rate;
								$rates[] = array(
									'rate' => $rate_sum,
									'name' => $no_actress->name,
								);
							}
							$randnum = rand(0, $rate_sum - 1);
							foreach ($rates as $rate) {
								if ($rate['rate'] > $randnum) {
									$format = str_replace('#actress#', $rate['name'], $format);
									break;
								}
							}
						}
						
						$title_replaced = $format. $title_replaced;
					}
				
					// NG判定
					// 投稿ワード
					$postwords = Postword::where('sites_acc', $acc)->get();
					$postok = true;
					$word_tags = array();
					foreach ($postwords as $postword) {
						$postok = false;
						$word_tag = $postword->tag;
						if (preg_match("/$word_tag/", $title_replaced)) {
							$postok = true;
							break;
						}
						$word_tags[] = $word_tag;
					}
					if ($postok !== true) {
						// NG
						$blog_articles_error['ng'][] = array(
							'blogid' => $blogid,
							'acc' => $acc,
							'url' => $article_url,
							'rssurl' => $rssurl,
							'postword' => implode(',', $word_tags),
							'ngword' => null,
						);
						continue;
					}
					// 除外ワード
					$ngwords = Ngword::where('sites_acc', $acc)->get();
					foreach ($ngwords as $ngword) {
						$word_tag = $ngword->tag;
						if (preg_match("/$word_tag/", $title_replaced)) {
							// NG
							$blog_articles_error['ng'][]= array(
								'blogid' => $blogid,
								'acc' => $acc,
								'url' => $article_url,
								'rssurl' => $rssurl,
								'postword' => null,
								'ngword' => $word_tag,
							);
							continue 2;
						}
					}
					
					// #imgurl#
					$imgurl = "";
					foreach (array($description, $content) as $target) {
						if (preg_match_all($this->regex_imgurl, $target, $matches, PREG_SET_ORDER)) {
							foreach ($matches as $match) {
								// microadのビーコン画像無視
								if (!isset($match[1]) || strpos($match[1], "microad") !== false) {
									Log::warning("microad img so ignore $article_url at $rssurl");
									continue;
								}
								$imgurl = $match[1];
								break;
							}
						}
					}
					
					// ここまでこれた記事のみ取得を試みる
					if (!isset($blog_articles[$article_url])) {
						$blog_articles[$article_url] = array(
							'http_code' => null,
							'response' => null,
							'blog' => array(),
						);
						$article_cnt_per_rss++;
					}
					$blog_articles[$article_url]['blog'][$blogid] = array(
						'acc' => $acc,
						'rssurl' => $rssurl,
						'title' => $title_replaced,
						'title_org' => $title,
						'description' => $description,
						'imgurl' => $imgurl,
						'tag' => $tag,
						'created_at' => $created_at,
					);
				}
			}
		}
		Log::info("rss parse done");

		// blog fetch
		if ($this->multiFetch(array_keys($blog_articles), $blog_articles, $blog_articles_error['fetch']) !== true) {
			echo("failed blog multifetch");
			exit;
		}
		// タイムアウトだけはエラーに入ってこず結果もセットされないので
		foreach ($blog_articles as $url => $article) {
			if (!isset($article['http_code'])) {
				$blog_articles_error['fetch'][] = $url;
				Log::warning("timeout fetch article $url");
			}
		}
		Log::info("blog fetch done");

		// DB登録データ作成
		$utime = time();
		$updated_at = date('Y-m-d H:i:s', $utime);
		foreach ($blog_articles as $article_url => $article) {
			// 記事取得に失敗している場合はパス
			if (isset($blog_articles_error['fetch'][$article_url])) {
				continue;
			}
			
			$html = $article['response'];
			
			// movデータ
			$mov_site = "";
			$movid = "";
			$movlink = "";
			foreach($this->regex_mov as $key => $regex) {
				// #movid# #movlink#
				if (preg_match($regex['id'], $html, $match)) {
					$movid = $match[1];
					$mov_site = $key;
					break;
				} elseif (preg_match($regex['link'], $html, $match)) {
					$movlink = $match[1];
					$mov_site = $key;
					break;
				}
			}
			
			foreach ($article['blog'] as $blogid => $item) {
				$title_org = $item['title_org'];
				$title = $item['title'];
				
				if (Config::get('app.manu')) {
					//手動ツールは記事編集時にセットする
					$new = 0;
					$seo_title = '';
					$seo_desc = '';
					$seo_keyword = '';
				} else {
					$new = 1;
					$seo_title = str_replace('#title#', $title, 
						str_replace('#title_org#', $title_org, 
							str_replace('#tag#', $item['tag'], $sites[$item['acc']]['seotitle'])));
					$seo_desc = str_replace('#title#', $title, 
						str_replace('#title_org#', $title_org, 
							str_replace('#tag#', $item['tag'], $sites[$item['acc']]['seodesc'])));
					$seo_keyword = str_replace('#title#', $title, 
						str_replace('#title_org#', $title_org, 
							str_replace('#tag#', $item['tag'], $sites[$item['acc']]['seokeyword'])));
				}
				$db_articles[] = array(
					'acc' => $item['acc'],
					'blogid' => $blogid,
					'url' => $article_url,
					'title' => $title,
					'title_org' => $title_org,
					'title_rewrite' => '',
					'description' => $item['description'],
					'imgurl' => $item['imgurl'],
					'movid' => $movid,
					'movlink' => $movlink,
					'movSite' => $mov_site,
					'tag' => $item['tag'],
					'utime' => $utime,
					'posted_at' => '0000/00/00 00:00:00',
					'created_at' => $item['created_at'],
					'updated_at' => $updated_at,
					'seo_title' => $seo_title,
					'seo_desc' => $seo_desc,
					'seo_keyword' => $seo_keyword,
					'researved_at' => '0000-00-00 00:00:00',
					'category' => '',
					'new' => $new,
				);
			}
		}
		
		// DB登録
		foreach ($db_articles as $db_article) {
			$blogid = $db_article['blogid'];
			$article_url = $db_article['url'];
			$query = "SELECT id FROM article2 USE INDEX (blogid_url_unique) WHERE blogid = '$blogid' AND url = '$article_url'";
			$arr = DB::select($query);
			if (isset($arr[0]) && Input::has('force_update')) {
				$id = $arr[0]->id;
				Article2::where('id', $id)->update($db_article);
			} else {
				Article2::create($db_article);
			}
		}
		Log::info("update db done");

		// 処理終了時間
		$time_end = microtime(true);
		
		// 結果簡易出力
		$db_articles_display = $db_articles;
		foreach ($db_articles_display as &$display) {
			unset($display['blogid']);
			unset($display['description']);
			unset($display['utime']);
			unset($display['posted_at']);
			unset($display['seo_title']);
			unset($display['seo_desc']);
			unset($display['seo_keyword']);
			unset($display['researved_at']);
			unset($display['category']);
		}
		$blog_articles_error_display = $blog_articles_error;
		unset($blog_articles_error_display['registered']);
		$out = <<<EOS
<pre>
%f sec

全体
----------
rss取得総数： %d
rss取得失敗数: %d
記事取得総数: %d
記事登録済判定数: %d
記事リクエスト失敗数: %d
記事リクエスト超過数: %d
記事NG判定数: %d


新規登録
----------
%d 件


rss失敗
----------
%d 件
<code>
%s
</code>

記事失敗
----------
%d 件
<code>
%s
</code>
</pre>
EOS
;
		if (!Input::has('cmd')) {
			printf($out,
				$time_end - $time_start,
				count($rss_list),
				count($rss_list_error),
				count($blog_articles),
				count($blog_articles_error['registered']),
				count($blog_articles_error['fetch']),
				count($blog_articles_error['over']),
				count($blog_articles_error['ng']),
				count($db_articles_display),
				count($rss_list_error),
				var_export($rss_list_error, true),
				count($blog_articles_error['fetch']) + count($blog_articles_error['over']) + count($blog_articles_error['ng']),
				var_export($blog_articles_error_display, true)
			);
		}
		$msg = sprintf("rssGet done: rss(%d), rss_err(%d), article(%d), article_err(%d)", count($rss_list), count($rss_list_error), count($db_articles_display), count($blog_articles_error['fetch']) + count($blog_articles_error['over']) + count($blog_articles_error['ng']));
		Log::info($msg);
		exit;
	}
	
	
	
	public function rssPost(){
		// 処理開始時間
		$time_start = microtime(true);
		Log::info('rssPost start');
		
		// 処理全体のタイムアウト時間を設定する
		set_time_limit(self::MAX_TIME_LIMIT);
		
		// 集計用
		$article_done = array();
		$article_ignore = array();
		$article_error = array();
		
		// サイト取得
		if (!Input::has('site') && !Input::has('acc')) {
			$db_sites = Sites::all();
		} else {
			if (Input::has('site')) {
				$db_sites = Sites::where('id', Input::get('site'))->get();
			} else {
				$db_sites = Sites::where('acc', Input::get('acc'))->get();
			}
		}
		
		foreach ($db_sites as $site) {
			$acc = $site->acc;
			
			// 投稿しない設定の場合はスルー
			if (empty($site->postType)) {
				continue;
			}
			
			// 投稿候補記事取得
			if (Config::get('app.manu')) {
				// 手動投稿ツール。予約投稿時間が設定された記事のみ
				$query = "SELECT id, url, title, imgurl, movid, movlink, movSite, tag, category, description, seo_title, title_rewrite, seo_desc, seo_keyword, researved_at FROM article2 USE INDEX (acc_new) WHERE acc = '$acc' AND new = '1' AND researved_at <> '0000-00-00 00:00:00' AND researved_at <> '' AND researved_at IS NOT NULL";
			} else {
				// 自動投稿ツール
				$query = "SELECT id, url, title, imgurl, movid, movlink, movSite, tag, category, description, seo_title, title_rewrite, seo_desc, seo_keyword, researved_at FROM article2 USE INDEX (acc_new) WHERE acc = '$acc' AND new = '1'";
			}
			$article_list = DB::select($query); 
			
			foreach ($article_list as $article) {
				$msg = sprintf("post start %d", $article->id);
				Log::debug($msg);
				
				// 記事チェック
				// チェックに引っかかった記事はnewフラグを落として次へ
				// 画像
				if (!empty($site->isNeedimg) || !empty($site->isEyecatch)) {
					if (empty($article->imgurl)) {
						$article_ignore[] = array(
							'reason' => 'no image',
							'acc' => $site->acc,
							'article' => $article,
						);
						Article2::where('id', $article->id)->update(array('new' => 0));
						$msg = sprintf("no image ignore: article_id(%s)", $article->id);
						LOG::info($msg);
						continue;
					}
				}
				// タイトル長さ
				if (
					(!empty($article->title_rewrite) && mb_strlen($article->title_rewrite) > $site->titleLength) ||
					(empty($article->title_rewrite) && mb_strlen($article->title) > $site->titleLength)
				) {
					$article_ignore[] = array(
						'reason' => 'over title length',
						'acc' => $site->acc,
						'article' => $article,
					);
					Article2::where('id', $article->id)->update(array('new' => 0));
					$msg = sprintf("too long title length ignore: article_id(%s)", $article->id);
					LOG::info($msg);
					continue;
				}
				// 動画情報
				if (!empty($site->isNeedmov)) {
					if ( empty($article->movSite) || ( empty($article->movid) && empty($article->movlink) ) ) {
						$article_ignore[] = array(
							'reason' => 'no movie info',
							'acc' => $site->acc,
							'article' => $article,
						);
						Article2::where('id', $article->id)->update(array('new' => 0));
						$msg = sprintf("no movie info ignore: article_id(%s)", $article->id);
						LOG::info($msg);
						continue;
					}
				}
				
				// 投稿内容作成
				$wptitle = $site->wptitle;
				if (!empty($article->title_rewrite)) {
					$wptitle = str_replace('#title#', $article->title_rewrite, $wptitle);
				} else {
					$wptitle = str_replace('#title#', $article->title, $wptitle);
				}
				
				$wpdesc = "";
				if (!empty($site->isNeedmov)) {
					if (!empty($article->movid)) {
						// 動画テンプレートを使う
						$col = $article->movSite;
						$wpdesc = $site->$col;
						$wpdesc_trim = trim(mb_convert_kana($wpdesc, 's', 'UTF-8'));
						if (empty($wpdesc_trim)) {
							$article_ignore[] = array(
								'reason' => 'empty movie template',
								'acc' => $site->acc,
								'article' => $article,
							);
							Article2::where('id', $article->id)->update(array('new' => 0));
							$msg = sprintf("not set template ignore: article_id(%s), movSite(%s)", $article->id, $article->movSite);
							LOG::info($msg);
							continue;
						}
						$wpdesc = str_replace('#title#', $article->title, $wpdesc);
						$wpdesc = str_replace('#imgurl#', $article->imgurl, $wpdesc);
						$wpdesc = str_replace('#url#', $article->url, $wpdesc);
						$wpdesc = str_replace('#movid#', $article->movid, $wpdesc);
						$wpdesc = str_replace('#movSite#', $article->movSite, $wpdesc);
					} else {
						// 動画リンクテンプレートを使う
						$col = $article->movSite. '__movlink';
						$wpdesc = $site->$col;
						$wpdesc_trim = trim(mb_convert_kana($wpdesc, 's', 'UTF-8'));
						if (empty($wpdesc_trim)) {
							$article_ignore[] = array(
								'reason' => 'empty movie link template',
								'acc' => $site->acc,
								'article' => $article,
							);
							Article2::where('id', $article->id)->update(array('new' => 0));
							$msg = sprintf("not set link template ignore: article_id(%s), movSite(%s)", $article->id, $article->movSite);
							LOG::info($msg);
							continue;
						}
						$wpdesc = str_replace('#title#', $article->title, $wpdesc);
						$wpdesc = str_replace('#imgurl#', $article->imgurl, $wpdesc);
						$wpdesc = str_replace('#url#', $article->url, $wpdesc);
						$wpdesc = str_replace('#movlink#', $article->movlink, $wpdesc);
						$wpdesc = str_replace('#movSite#', $article->movSite, $wpdesc);
					}
				} else {
					// 投稿内容設定を使う
					$wpdesc = $site->wpdesc;
					$wpdesc = str_replace('#title#', $article->title, $wpdesc);
					$wpdesc = str_replace('#url#', $article->url, $wpdesc);
					$wpdesc = str_replace('#imgurl#', $article->imgurl, $wpdesc);
					$wpdesc = str_replace('#content#', $article->description, $wpdesc);
				}
				
				//var_dump($wptitle, $wpdesc);
				
				$client_ixr = new IXR_Client($site->wphost);
				$img_ixr_id = "";
				if (!empty($site->isEyecatch)) {
					// アイキャッチ投稿
					// http://nekoriki.net/50 を参考にしている
					$img_info = @getimagesize($article->imgurl);
					if (!$img_info) {
						// 画像取得失敗
						$article_error[] = array(
							'reason' => "failed fetch image file",
							'acc' => $site->acc,
							'article' => $article,
						);
						Article2::where('id', $article->id)->update(array('new' => 0));
						$msg = sprintf("failed fetch image file: article_id(%s), imgurl(%s)", $article->id, $article->imgurl);
						Log::warning($msg);
						continue;
					}
					$bits_ixr = new IXR_Base64(@file_get_contents($article->imgurl));
					$imgurl_parsed = parse_url($article->imgurl);
					$img_name = basename($imgurl_parsed['path']);
					
					$status_ixr = $client_ixr->query(
						"wp.uploadFile",
						1,
						$site->wpuser,
						$site->wppass,
						array(
							'name' => $img_name,
							'type' => $img_info['mime'],
							'bits' => $bits_ixr,
							'overwrite' => true,
						)
					);
					if(!$status_ixr){
						$article_error[] = array(
							'reason' => "failed post eyecatch",
							'acc' => $site->acc,
							'msg' => $client_ixr->getErrorCode(). ': '. $client_ixr->getErrorMessage(),
							'article' => $article,
						);
						Article2::where('id', $article->id)->update(array('new' => 0));
						$msg = sprintf("failed post eyecatch: article_id(%s), imgurl(%s), msg(%s)", $article->id, $article->imgurl, $client_ixr->getErrorCode(). ': '. $client_ixr->getErrorMessage());
						Log::warning($msg);
						continue;
					}
					$img_ixr = $client_ixr->getResponse();
					$img_ixr_id = $img_ixr['id'];
				}
				
				// 記事投稿
				// http://nekoriki.net/47 を参考にしている
				// クエリ作成
				$query = array(
					'post_title' => $wptitle, // タイトル
					'post_content' => $wpdesc, // 本文
				);
				// -投稿状態
				if (!empty($article->researved_at) && $article->researved_at != '0000-00-00 00:00:00') {
					$query['post_status'] = 'future';
					$post_date_ixr = new IXR_Date(strtotime($article->researved_at));
					$query['post_date'] = $post_date_ixr;
				} elseif ($site->post_status == 'draft') {
					$query['post_status'] = 'draft';
				} else {
					$query['post_status'] = 'publish';
				}
				// -タグ
				if (!empty($article->tag)) {
					$tag_arr =  explode(',', $article->tag);
					foreach ($tag_arr as $tag) {
						if (!empty($tag)) {
							$query['terms_names']['post_tag'][] = $tag;
						}
					}
				}
				// -カテゴリ
				if (!empty($article->category)) {
					$category_id_arr = explode(',', $article->category);
					$query['terms']['category'] = $category_id_arr;
				} elseif (!empty($site->isPostCategory) && !empty($article->movSite)) {
					$query['terms_names']['category'] = array($article->movSite);
				}
				// -SEO
				if (!empty($article->seo_title)) {
					$query['custom_fields'][] = array('key' => '_aioseop_title', 'value' => $article->seo_title);
				}
				if (!empty($article->seo_keyword)) {
					$query['custom_fields'][] = array('key' => '_aioseop_keywords', 'value' => $article->seo_keyword);
				}
				if (!empty($article->seo_desc)) {
					$query['custom_fields'][] = array('key' => '_aioseop_description', 'value' => $article->seo_desc);
				}
				// 投稿リクエスト
				$status_ixr = $client_ixr->query(
					"wp.newPost", //使うAPIを指定（wp.newPostは、新規投稿）
					1, // blog ID: 通常は1、マルチサイト時変更
					$site->wpuser, // ユーザー名
					$site->wppass, // パスワード
					$query
				);
				$postid_ixr = "";
				if(!$status_ixr){
					$article_error[] = array(
						'reason' => "failed post",
						'acc' => $site->acc,
						'msg' => $client_ixr->getErrorCode(). ': '. $client_ixr->getErrorMessage(),
						'article' => $article,
					);
					$msg = sprintf("failed post: article_id(%s), msg(%s)", $article->id, $client_ixr->getErrorCode(). ': '. $client_ixr->getErrorMessage());
					Log::warning($msg);
					continue;
				}
				$postid_ixr = $client_ixr->getResponse(); //返り値は投稿ID
				
					
				if (!empty($site->isEyecatch)) {
					// アイキャッチ設定
					$status_ixr = $client_ixr->query(
						"wp.editPost",
						1,
						$site->wpuser, 
						$site->wppass, 
						$postid_ixr,
						array("post_thumbnail" => $img_ixr_id)
					);
					if(!$status_ixr){
						$article_error[] = array(
							'reason' => "failed set eyecatch",
							'acc' => $site->acc,
							'msg' => $client_ixr->getErrorCode(). ': '. $client_ixr->getErrorMessage(),
							'article' => $article,
						);
						Article2::where('id', $article->id)->update(array('new' => 0));
						$msg = sprintf("failed set eyecatch: article_id(%s), imgurl(%s), postid(%s), msg(%s)", $article->id, $article->imgurl, $postid_ixr, $client_ixr->getErrorCode(). ': '. $client_ixr->getErrorMessage());
						Log::warning($msg);
						continue;
					}
					$thumb_ixr = $client_ixr->getResponse();
				}
				
				// newフラグを落とす。投稿日時を更新する。予約投稿日時をクリアする。
				Article2::where('id', $article->id)->update(array(
					'new' => 0,
					'posted_at' => date('Y-m-d H:i:s', time()),
					'researved_at' => '0000-00-00 00:00:00',
				));
				
				$article_done[] = array(
					'acc' => $site->acc,
					'article' => $article,
					'postid' => $postid_ixr,
					'img' => $img_ixr_id,
					'query' => $query,
				);
				$msg = sprintf("post done %d", $article->id);
				Log::debug($msg);
			}
		}
		
		// 処理終了時間
		$time_end = microtime(true);
		
		// 結果簡易出力
		$article_done_display = $article_done;
		$article_ignore_display = $article_ignore;
		$article_error_display = $article_error;
		$out = <<<EOS
<pre>
%f sec

全体
----------
新着記事総数： %d
記事投稿数: %d
記事無視数: %d
記事投稿エラー数: %d


投稿成功
----------
%d 件
<code>
%s
</code>


無視
----------
%d 件
<code>
%s
</code>


投稿失敗
----------
%d 件
<code>
%s
</code>
</pre>
EOS
;
		if (!Input::has('cmd')) {
			printf($out,
				$time_end - $time_start,
				count($article_done_display) + count($article_ignore_display) + count($article_error_display),
				count($article_done_display),
				count($article_ignore_display),
				count($article_error_display),
				count($article_done_display),
				var_export($article_done_display, true),
				count($article_ignore_display),
				var_export($article_ignore_display, true),
				count($article_error_display),
				var_export($article_error_display, true)
			);
		}
		$msg = sprintf('rssPost done: article(%d), article_error(%d)', count($article_done_display), count($article_error_display));
		Log::info($msg);
		exit;
	}
	
	
	
	protected function multiFetch($urls, &$urls_result, &$urls_error) {
		// curl_multiの処理については以下を参考にしている
		// http://qiita.com/Hiraku/items/1c67b51040246efb4254
		
		//// わかりやすい書き方が思いつかなかったコード
		// ドメインごとの最大並列数を守る (他サーバに負荷を掛けない)
		// また全体の最大並列数は守る (自サーバに負荷を掛け過ぎない)
		// 前提として、ドメインごとの最大並列数 < 全体最大並列数
		$urls_para_safe = array();
		$domain_cur = array(
			'cnt' => 0,  // 並列数カウンタ
			'num' => 0,  // 要素番号
		);
		$min_number = 0;  // 空いている配列の最小要素番号
		foreach ($urls as $url) {
			$url_parsed = parse_url($url);
			if ($url_parsed === false || empty($url_parsed['host'])) {
				$urls_error[$url] = true;
				Log::warning("invalid url $url");
				continue;
			}
			$domain = $url_parsed['host'];
			if (!isset($domain_cur[$domain]['cnt'])) {
				// 全体を通して初めてのドメイン
				$domain_cur[$domain]['cnt'] = 1;
				$domain_cur[$domain]['num'] = $min_number;
			} else {
				$domain_cur[$domain]['cnt']++;
				if ($domain_cur[$domain]['cnt'] > self::MULTI_CURL_MAX_DOMAIN_PARA) {
					// 今の配列ではドメインの最大並列数を超えたので、
					// 新しい配列にurlを格納する。カウンタは1に戻す
					$domain_cur[$domain]['num']++;
					if ($domain_cur[$domain]['num'] < $min_number) {
						// min_numberより小さいということは、
						// 今予定している格納先の配列はすでにサイズオーバしているので、
						// min_numberの配列に格納するようにする
						$domain_cur[$domain]['num'] = $min_number;
					}
					$domain_cur[$domain]['cnt'] = 1;
				}
			}
			
			$number = $domain_cur[$domain]['num'];
			$urls_para_safe[$number][] = $url;
			
			if (count($urls_para_safe[$number]) >= self::MULTI_CURL_MAX_PARA) {
				// 要素番号の若い配列から埋まっていく。また、同時に複数埋まることはない
				// 配列にurlを格納して最大並列数に達したということはnumberはmin_numberだったということであり、
				// min_numberに対して1だけ足せばよい
				$min_number++;
			}
		}
		//// わかりやすい書き方が思いつかなかったコードここまで
		
		foreach ($urls_para_safe as $urls_chunk) {
			$msg = sprintf("try multi fetch %s", implode(" ", $urls_chunk));
			Log::info($msg);
			
			// curl_multiハンドラ
			$mh = curl_multi_init();
			
			// curl_multiハンドラに各curlハンドラ格納
			foreach ($urls_chunk as $url) {
				$ch = curl_init();
				curl_setopt_array($ch, array(
					CURLOPT_URL            => $url,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_TIMEOUT        => self::CURL_TIMEOUT,
					CURLOPT_CONNECTTIMEOUT => self::CURL_TIMEOUT,
				));
				curl_multi_add_handle($mh, $ch);
			}
			
			// curl_multi実行
			curl_multi_exec($mh, $running); // multiリクエストスタート
			
			// curl_multi実行完了待ち
			$failcnt = 0;
			do switch (curl_multi_select($mh, self::MULTI_CURL_TIMEOUT)) {
				case -1: // selectに失敗。最初は必ず通過する
					$failcnt++;
					if ($failcnt > self::MULTI_CURL_MAX_FAIL_CNT) {
						// curl_multi結果待ち失敗
						curl_multi_close($mh);
						continue 3;
					}
					usleep(10);
					do {
						$stat = curl_multi_exec($mh, $running);
					} while ($stat === CURLM_CALL_MULTI_PERFORM);
					continue 2;

				case 0:  //タイムアウト
						Log::warning("timeout curl_multi_select");
						curl_multi_close($mh);
						continue 3;

				default: //curlの結果が返ってきた
					do {
						$stat = curl_multi_exec($mh, $running);
					} while ($stat === CURLM_CALL_MULTI_PERFORM);

					do if ($raised = curl_multi_info_read($mh, $remains)) {
						$info = curl_getinfo($raised['handle']);
						$url = $info['url'];
						$http_code = $info['http_code'];
						$response = curl_multi_getcontent($raised['handle']);
						
						$urls_result[$url] = array_merge(
							$urls_result[$url],
							array(
								'http_code' => $http_code,
								'response' => $response,
							)
						);
						
						if ($urls_result[$url]['response'] === false) {
							//取得失敗
							$urls_error[$url] = true;
							Log::warning("failed fetch $url: http_code($http_code)");
						}
						
						Log::info("fetch done $url");
						curl_multi_remove_handle($mh, $raised['handle']);
						curl_close($raised['handle']);
					} while ($remains);
			} while ($running);
			
			curl_multi_close($mh);
		}
		
		return true;
	}
}
