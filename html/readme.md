# auto

## target server

    host : 153.122.10.11
    http://rsspos.net/
    http://153.122.10.11:8888/pma21194a/

## debug

    delete from article where blogid>4;


## spec

### �d���^�C�g�����O

    article�e�[�u���œ���blogid�̓��^�C�g���͒ǉ����Ȃ�
    ���݂̏d��URL�`�F�b�N�͂��̂܂܁B

### �i�肱�ݓ��e

    �^�C�g���Ɏw��L�[���[�h(ngword)������ꍇ�́A�L���擾���Ȃ�
    �^�C�g���ɓ��e���[�h�����݂���ꍇ�̂݃u���O�ɓ��e�B

### �^�C�g���ɏ��D����t�^

    �t�H�[�}�b�g�ݒ�ŗL�����m�F�B(��̏ꍇ�͕t�^���Ȃ�)
    ���D������
    ���D�����q�b�g���Ȃ��ꍇ�́A�w�蕶����i�����{���[�g�j�̒����烉���_���ŕt�^�B

### ���C�u�h�A���e

    ���݁AWP , FC2�݂̂����A���C�u�h�A�u���O���Ή���������

### �������i��

    �u���O�ɓ��e���镶���񂪎w�蕶�����ȏ�̏ꍇ�́A���e���Ȃ��@�\


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

    ��

    # for rsspos.net NEW
    */15 * * * * curl http://rsspos.net/cron/cnt
    5,35 * * * * curl http://rsspos.net/cron/rssGet;curl http://rsspos.net/cron/rssPost


## livedoor blog

    http://avmovie19.blog.jp


# manu

## server

    http://153.122.10.11/
    http://153.122.10.11:8888/pma21194a/

## memo


## crontab

    SHELL=/bin/bash
    # for IP
    */15 * * * * curl http://153.122.10.11/cron/cnt.php
    5,35 * * * * curl http://153.122.10.11/cron/rssGet.php;curl http://153.122.10.11/cron/rssGet2.php
    */5 * * * * curl http://153.122.10.11/cron/posts.php

