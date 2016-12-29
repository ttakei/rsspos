<?php

class CronController2 extends BaseController {

	const MAX_TIME_LIMIT = 600;  // 処理全体の最大処理時間(秒)
	const CURL_TIMEOUT = 10;
	const MULTI_CURL_TIMEOUT = 20;
	const MULTI_CURL_MAX_FAIL_CNT = 3;
	const MULTI_CURL_MAX_DOMAIN_PARA = 1;  // ドメイン単位の最大並列数
	const MULTI_CURL_MAX_PARA = 50;  // リクエスト最大並列数
	const MAX_NUM_ARTICLE_PER_RSS = 20;  //1つのrssでfetchする最大記事件数
	
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
		
		// 処理全体のタイムアウト時間を設定する
		set_time_limit(self::MAX_TIME_LIMIT);
		
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
			if (!$sites[$acc]['isactive']) {
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
			exit;
		}
		// タイムアウトだけはエラーに入ってこず結果もセットされないので
		foreach ($rss_list as $rssurl => $rss) {
			if (!isset($rss['http_code'])) {
				$rss_list_error[] = $rssurl;
			}
		}
		echo("<pre>rss fetch done</pre>\n");
		
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
						$tag_arr[] = $item_tag->get_label();
					}
				}
				$tag = implode(',', $tag_arr);
				
				if ($article_cnt_per_rss > self::MAX_NUM_ARTICLE_PER_RSS) {
					// 1つのrssで処理する記事最大件数を超えた場合
					$blog_articles_error['over'][] = array(
						'url' => $article_url,
						'rssurl' => $rssurl,
					);
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
					
					// NG判定
					// 投稿ワード
					$postwords = Postword::where('sites_acc', $acc)->get();
					$postok = true;
					$word_tags = array();
					foreach ($postwords as $postword) {
						$postok = false;
						$word_tag = $postword->tag;
						if (preg_match("/$word_tag/", $title)) {
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
						if (preg_match("/$word_tag/", $title)) {
							// NG
							$blog_articles_ng[] = array(
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
						'title' => $title,
						'description' => $description,
						'imgurl' => $imgurl,
						'tag' => $tag,
						'created_at' => $created_at,
					);
				}
			}
		}
		echo("<pre>rss parse done</pre>\n");

		// blog fetch
		if ($this->multiFetch(array_keys($blog_articles), $blog_articles, $blog_articles_error['fetch']) !== true) {
			echo("failed blog multifetch");
			exit;
		}
		// タイムアウトだけはエラーに入ってこず結果もセットされないので
		foreach ($blog_articles as $url => $article) {
			if (!isset($article['http_code'])) {
				$blog_articles_error['fetch'][] = $url;
			}
		}
		echo("<pre>blog fetch done</pre>\n");

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
				$title_org = $item['title'];
				$title = $title_org;
				
				// タイトル置換
				$replace_words = ReplaceWords::all();
				foreach ($replace_words as $replace_word) {
					$from_word = $replace_word->from;
					$to_words = explode(',', $replace_word->to);
					$to_word = $to_words[rand(0, count($to_words) - 1)];
					$title = str_replace($from_word, $to_word, $title);
				}
				
				// タイトル女優名付与
				// #actress#
				if (!empty($sites[$item['acc']]['actressFormat'])) {
					$format_org = $sites[$item['acc']]['actressFormat'];
					$format = $format_org;
					$actress_list = Actress::all();
					foreach ($actress_list as $actress) {
						if (strpos($title, $actress->name) !== false) {
							$format = str_replace('#actress#', $actress->name, $format);
							break;
						}
					}
					// 女優名がタイトルに含まれなかった場合
					if ($format === $format_org) {
						$no_actress_list = Noactress::where('sites_acc', $item['acc'])->get();
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
					
					$title = $format. $title;
				}
				
				$db_articles[] = array(
					'acc' => $item['acc'],
					'blogid' => $blogid,
					'url' => $article_url,
					'title' => $title,
					'title_org' => $title_org,
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
					'seo_title' => '',
					'seo_desc' => '',
					'seo_keyword' => '',
					'researved_at' => '0000-00-00 00:00:00',
					'category' => '',
					'new' => 1,
				);
			}
		}
		echo("<pre>make db data done</pre>\n");
		
//		var_dump($db_articles);
//		var_dump($blog_articles_error);
		
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
		//echo("<pre>update db done</pre>\n");
		//echo("<pre>all done\n</pre>\n");

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
<code>
%s
</code>


失敗
----------
%d 件
<code>
%s
</code>
</pre>
EOS
;
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
			var_export($db_articles_display, true),
			count($blog_articles_error['fetch']) + count($blog_articles_error['over']) + count($blog_articles_error['ng']),
			var_export($blog_articles_error_display, true)
		);
	}
	
	
	
	public function rssPost(){
		// 処理開始時間
		$time_start = microtime(true);
		
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
			$query = "SELECT id, url, title, imgurl, movid, movlink, movSite, tag, description FROM article2 USE INDEX (acc_new) WHERE acc = '$acc' AND new = '1'";
			$article_list = DB::select($query); 
			
			foreach ($article_list as $article) {
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
						continue;
					}
				}
				// タイトル長さ
				if (mb_strlen($article->title) > $site->titleLength) {
					$article_ignore[] = array(
						'reason' => 'over title length',
						'acc' => $site->acc,
						'article' => $article,
					);
					Article2::where('id', $article->id)->update(array('new' => 0));
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
						continue;
					}
				}
				
				// 投稿内容作成
				$wptitle = $site->wptitle;
				$wptitle = str_replace('#title#', $article->title, $wptitle);
				
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
				
				// 投稿
				// http://nekoriki.net/47 を参考にしている
				$client_ixr = new IXR_Client($site->wphost);
				$query = array(
					'post_status' => $site->post_status, // 投稿状態
					'post_title' => $wptitle, // タイトル
					'post_content' => $wpdesc, // 本文
				);
				if (!empty($article->tag)) {
					$query['terms_names']['post_tag'] = explode(',', $article->tag);
				}
				if (!empty($site->isPostCategory) && !empty($article->movSite)) {
					$query['terms_names']['category'] = array($article->movSite);
				}
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
					continue;
				}
				$postid_ixr = $client_ixr->getResponse(); //返り値は投稿ID
				
				// アイキャッチ投稿
				// http://nekoriki.net/50 を参考にしている
				$img_ixr_id = "";
				if (!empty($site->isEyecatch)) {
					$img_info = @getimagesize($article->imgurl);
					if (!$img_info) {
						// 画像取得失敗
						$article_error[] = array(
							'reason' => "failed fetch image file",
							'acc' => $site->acc,
							'article' => $article,
						);
						Article2::where('id', $article->id)->update(array('new' => 0));
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
							'post_id' => $postid_ixr,
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
						continue;
					}
					$img_ixr = $client_ixr->getResponse();
					$img_ixr_id = $img_ixr['id'];
					
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
						continue;
					}
					$thumb_ixr = $client_ixr->getResponse();
				}
				
				// newフラグを落とす。投稿日時を更新する
				Article2::where('id', $article->id)->update(array(
					'new' => 0,
					'posted_at' => date('Y-m-d H:i:s', time()),
				));
				
				$article_done[] = array(
					'acc' => $site->acc,
					'article' => $article,
					'postid' => $postid_ixr,
					'img' => $img_ixr_id,
				);
				continue;
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
				echo("<pre>invalid url $url</pre>\n");
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
				case -1: // selectに失敗。通常起きないが稀に起きるらしい
					$failcnt++;
					if ($failcnt > self::MULTI_CURL_MAX_FAIL_CNT) {
						// curl_multi結果待ち失敗
						echo ("failed curl_multi_select");
						return false;
					}
					usleep(10);
					do {
						$stat = curl_multi_exec($mh, $running);
					} while ($stat === CURLM_CALL_MULTI_PERFORM);
					continue 2;

				case 0:  //タイムアウト
						//
						echo ("timeout curl_multi_select");
						continue 2;

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
						}
						
						curl_multi_remove_handle($mh, $raised['handle']);
						curl_close($raised['handle']);
					} while ($remains);
			} while ($running);
			
			curl_multi_close($mh);
		}
		
		return true;
	}
}
