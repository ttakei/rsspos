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

