## target server

    host : 153.122.10.11
    http://rsspos.net/
    http://153.122.10.11:8888/pma21194a/

## debug

    delete from article where blogid>4;


## spec

### 重複タイトル除外

    articleテーブルで同一blogidの同タイトルは追加しない
    現在の重複URLチェックはそのまま。

### 絞りこみ投稿

    タイトルに指定キーワード(ngword)がある場合は、記事取得しない
    タイトルに投稿ワードが存在する場合のみブログに投稿。

### タイトルに女優名を付与

    フォーマット設定で有無を確認。(空の場合は付与しない)
    女優名辞書
    女優名がヒットしない場合は、指定文字列（文字＋レート）の中からランダムで付与。

### ライブドア投稿

    現在、WP , FC2のみだが、ライブドアブログも対応させたい

### 文字数絞り

    ブログに投稿する文字列が指定文字数以上の場合は、投稿しない機能


## laravel install

    % curl -sS https://getcomposer.org/installer | php
    % mv composer.phar /usr/local/bin/composer

## file modified

    sftp-config.json
    app/config/database.php
    public/db.php
    public/rank.php
    public/cron/rssGet.php
    public/cron/rssGet2.php

## file upload

    % composer create-project laravel/laravel rsspos.net --prefer-dist 4.2

    app/config/app.php
    app/config/database.php
    app/models/*
    app/start/global.php
    app/views/*
    public/*

    % chmod 777 app/storage
    % mkdir public/timg; chmod 777 public/timg;

## cron

    # for rsspos.net
    */15 * * * * curl http://rsspos.net/cron/cnt.php
    5,35 * * * * curl http://rsspos.net/cron/rssGet.php;curl http://rsspos.net/cron/rssGet2.php
    */5 * * * * curl http://rsspos.net/cron/posts.php

    ↓

    # for rsspos.net NEW
    */15 * * * * curl http://rsspos.net/cron/cnt
    5,35 * * * * curl http://rsspos.net/cron/rssGet;curl http://rsspos.net/cron/rssPost


## livedoor blog

    http://avmovie19.blog.jp