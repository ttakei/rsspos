<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class RssGet extends Command {

    protected $name = 'rssget';
    protected $description = 'Command description.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->aspname = Config::get('app.aspname');

    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $startTime = microtime_float();
        DB::connection()->disableQueryLog();

        $arg = $this->argument();
        //dd($arg);

        $urlObj = DB::table('m_teams')->where('type_release',1)->whereNotIn('type_rss',[0,3])->where('type_blog',1)->where('rss','!=','')->where('rss','like','http%')->orderByRaw('id_sites ASC,id_branchs ASC,id_blocks ASC');
        if(!is_null($arg['target']) || (isset($arg['target']) && $arg["target"]=='sites')) $urlObj = $urlObj->where('id_sites',$arg['id']);
        if(!is_null($arg['target']) || (isset($arg['target']) && $arg["target"]=='branchs')) $urlObj = $urlObj->where('id_branchs',$arg['id']);
        if(!is_null($arg['target']) || (isset($arg['target']) && $arg["target"]=='teams')) $urlObj = $urlObj->where('id',$arg['id']);

        $urlObj = $urlObj->get();

        // foreach($urlObj as $v){
        //  $urls[$v->id] = $v->rss;
        //  if(!$multi) $rssObj[$v->id] = _curl_get_contents($v->rss);
        // }

        //置換ワード取得
        //$replaceWords = Replace::all();

        //if($multi) $rssObj = fetchMultiUrl($urls);

        $eTime = microtime_float()-$startTime;
        echo "rssurl取得完了:".$eTime."秒".PHP_EOL;

        //$taboo = Replace::where('del',0)->get();

        // RSS取得出来ていないURLは rssErrorCnt++
        // foreach($urls as $id=>$vv){
        //  if(!array_key_exists($id,$rssObj)){
        //      echo "$id は取得出来ていないようです | $vv".PHP_EOL;
        //      //Teams::where('id',$id)->increment('rssErrorCnt');
        //  }
        // }
        //return 'end'; // for debug

        foreach($urlObj as $blog):
            //$blog = Teams::where('id',$blog->id)->first();
            echo "$blog->id | $blog->rss".PHP_EOL;
            $feed = new SimplePie();
            $feed->enable_exceptions(false);
            //$feed->force_feed(true);
            //$feed->force_fsockopen(true);
            $feed->set_timeout(10);
            $feed->enable_cache(false);
            $feed->set_cache_location($_SERVER['DOCUMENT_ROOT'] . '/cache');
            //$feed->set_raw_data($blog_v);
            $feed->set_feed_url($blog->rss);

            try {
                $feed->init();
            } catch (Exception $e) {
                echo 'Caught exception: ',  $e->getMessage(), "\n";
                continue;
            }
            // if(!$feed->init()){
            //  echo '!!ERROR!!';
            //  var_dump($feed->error());
            //  // データベースにエラーカウント入れとくかな…

            //  continue;
            // }

            $dupCnt=0;

            //if(is_null($feed->get_items())){ echo 'FEED ERROR!';continue;};

            foreach($feed->get_items() as $item):
                if($dupCnt>5) break;
                $input = [];
                $img_all = array();
                $term = '';
                $termAry = array();
                $desc_tags = '';
                $description = '';

                $date_create = strtotime($item->get_date('Y-m-d H:i:s'));
                if($date_create>time()) {
                    echo "future date".PHP_EOL;
                    continue;   // 未来日付は取り込まない
                }
                if(strtotime("-1year") > $date_create) continue;
                $input['date_create'] = date('Y-m-d H:i:s',$date_create);

                $url = $item->get_permalink();
                $cnt = Info::where('url',$url)->count();
                if($cnt>0){
                    echo "dup | $url".PHP_EOL;
                    $dupCnt++;
                    continue;
                }
                $input['url'] = $url;

                $title = mb_convert_encoding($item->get_title(),"UTF-8","auto");
                $input['title'] = trim(mb_convert_kana( $title, "s"));  // 空白対応

                if (preg_match("/^PR:.+$/", $input['title']) != 0) continue;

                // $title_org = $title;
                // foreach($taboo as $tabooK=>$tabooV){
                //  $title = str_replace($tabooV->from,$tabooV->to,$title);
                // }

                $desc_tags = ($item->get_item_tags('', 'description')); // empty namespace is RSS2.0
                if ($desc_tags) {
                    $description = mb_convert_encoding($desc_tags[0]['data'],"UTF-8","auto");
                }

                if($description=='') $description = $title;

                if($item->get_description()){
                    $description = $item->get_description();
                }else{
                    $aaa = $item->get_item_tags('http://purl.org/rss/1.0/modules/content/','encoded');
                    if(is_array($aaa)&&$aaa[0]['data']!=''){
                        $description = $aaa[0]['data'];
                    }else{
                        $aaa = $item->get_item_tags('','description');
                        if(is_array($aaa)&&$aaa[0]['data']!='')
                            $description = $aaa[0]['data'];
                        else
                            $description = $item->get_title();
                    }
                }

                // $desc = ($item->get_item_tags('', 'description')); // empty namespace is RSS2.0
                // if ($desc) {
                //  $description = mb_convert_encoding($desc[0]['data'],"UTF-8","auto");
                // }elseif($item->get_description()){
                //  $description = mb_convert_encoding($item->get_description(),"UTF-8","auto");
                // }elseif($item->get_content()){
                //  $description = mb_convert_encoding($item->get_content(),"UTF-8","auto");
                // }
                $input['detail'] = trim(mb_convert_kana( $description, "s"));   // 空白対応

                //$img = ImgUrlExtraction($item->get_description(), $item->get_content());
                // if(!is_null($img)){
                //  $cnt = Info::where('img',$img)->count();
                //  if($cnt>0){
                //      echo "<span class=\"btn btn-danger btn-xs\">同じ画像は登録しません | $img</span><br>";
                //      continue;
                //  }
                // }

                // ない場合は、ブログに設定されているサムネイルを使う？
                //if(is_null($img)) $img=$this->cfg->siteurl.'images/noimage.gif';
                //$input['img'] = $img;

                // $_term = $item->get_categories();
                // if(isset($_term)){
                //  foreach($_term as $val){
                //      $termAry[] = preg_replace(['/,/','/\//'],['，','／'],$val->term); // タグ中にある半角コンマ、半角スラッシュを全角に変更しておく
                //  }
                //  $term = implode(',',$termAry);
                // }

                //$utime = time();

                // いろいろ準備
                //$input['id_teams']   = $blog->id_teams;
                $input['id_sites']   = $blog->id_sites;
                $input['id_branchs'] = $blog->id_branchs;
                $input['id_blocks']  = $blog->id_blocks;
                $input['id_teams']   = $blog->id;
                $input['id_members'] = 'cron';
                if($blog->id_sites=='japan'){
                    $input['type_infomation'] = 1;
                    $input['type_news'] = 8;
                    $input['type_publish'] = 1;
                }else{
                    $input['type_infomation'] = 3;
                    $input['type_news'] = 0;
                    $input['type_publish'] = 3;
                }

                //$input = compact('created_at','blog_id','title','title_org','url','img','term','description');
                // 念のための処理
                foreach($input as $input_k=>$input_v){
                    $input[$input_k] = html_entity_decode($input_v,ENT_COMPAT,'UTF-8');
                }

                //dd($input);
                var_dump($input['title']);

                // INSERT処理
                $info = Info::create($input);
                //echo $article->id;

                // タグを別テーブルに保存
                // foreach(explode(',',$term) as $v){
                //  $inputTerm = ['name'=>$v];
                //  $rterm = Terms::updateOrCreate(['name'=>$v],$inputTerm);
                //  Terms::where('name',$v)->increment('count');
                //  $inputTR= ['article_id'=>$article->id,'term_id'=>$rterm->id];
                //  TermRelationships::create($inputTR);
                // }

            endforeach;
        endforeach;

        //$endTime = microtime_float();
        $time = microtime_float() - $startTime;
        echo "$time sec".PHP_EOL;
        //$html = ob_get_contents();
        //ob_clean();

        return 'end';
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array(
            array('target', InputArgument::OPTIONAL, 'target argument.'),
            array('id', InputArgument::OPTIONAL, 'id argument.'),
        );
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array(
            //array('example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null),
        );
    }

}
