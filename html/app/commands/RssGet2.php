<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class RssGet2 extends Command {

    protected $name = 'rssget2';
    protected $description = 'Command description.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        // FacebookのRSS形式
        define('RSS_FB', 3);

        // 規定値
        define('ERROR_LIMIT', 10);
        define('COMMIT_LIMIT', 1000);
        define('OLD_LIMIT', '-1 year');
        define('DUPLICATE_LIMIT', '-1 minute');

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
        //date($arg);

        $urlObj = DB::table('m_teams')->where('type_release',1)->whereIn('type_rss',[1,2])->where('type_blog',1)->where('rss','!=','')->where('rss','like','http%')->orderByRaw('id_sites ASC,id_branchs ASC,id_blocks ASC');
        if($arg["target"]=='sites') $urlObj = $urlObj->where('id_sites',$arg['id']);
        if($arg["target"]=='branchs') $urlObj = $urlObj->where('id_branchs',$arg['id']);
        if($arg["target"]=='teams') $urlObj = $urlObj->where('id',$arg['id']);

        $urlObj = $urlObj->get();
        foreach($urlObj as $url){
            echo $url->rss.PHP_EOL;
        }

        foreach($urlObj as $blog):
            echo "$blog->id | $blog->rss".PHP_EOL;

            $rssAry = curl_get_contents($blog->rss,$blog->type_rss);

            if (empty($rssAry)) continue;

            $dupCnt=0;

            foreach($rssAry as $item):
                if($dupCnt>5) break;

                $cnt = Info::where('url',$item['url'])->count();
                if($cnt>0){
                    echo "dup | ".$item['url'].PHP_EOL;
                    $dupCnt++;
                    continue;
                }


                if (preg_match("/^PR:.+$/", $item['title']) != 0) continue;

                // 更新日時が無ければスキップ
                if (empty($item['date'])) continue;
                // 更新日時が未来の日時ならスキップ
                if (strtotime(date('YmdHis')) < strtotime($item['date'])) continue;
                // 更新日時が一定期間前の日時ならスキップ
                if (strtotime(date('YmdHis'). "-1 year") > strtotime($item['date'])) continue;
                // FacebookならタイトルHTMLエンティティのため変換
                //if ($r_teams['type_rss'] == RSS_FB) $item['title'] = html_entity_decode($item['title'], ENT_QUOTES, 'UTF-8');
                // タイトル文字数調整
                $title = trim($item['title']);
                $input['title'] = !empty($title) ? mb_substr($title, 0, 64, "UTF-8") : "【※タイトルはありません】";

                $detail = $item['detail'];
                $input['detail'] = trim(mb_convert_kana( $detail, "s"));    // 空白対応

                $input['date_create'] = $input['date_modify'] = $item['date'];
                $input['url'] = $item['url'];

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
                foreach($input as $input_k=>$input_v){
                    $input[$input_k] = html_entity_decode($input_v,ENT_COMPAT,'UTF-8');
                }

                //dd($input);
                var_dump($input['title']);

                // INSERT処理
                $info = Info::create($input);

            endforeach;
        endforeach;

        $time = microtime_float() - $startTime;
        echo "$time sec".PHP_EOL;

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
