#!/bin/sh

function log()
{
  str=$1
  date=`date "+%Y/%m/%d %H:%M:%S"`
  echo "[${date}] $str" >&2
}

host="new.rsspos.net"
lock="cron.$host.lock"

log "cron start"

if [ -e $lock ]; then
  log "$lock existed"
  lock_elapsed_time=$(( ( $(date +%s)0 - $(stat -c %Y $lock)0 ) / 10 ))
  lock_expire_time=$((60 * 60 * 1))
  if [ ${lock_elapsed_time} -gt ${lock_expire_time} ]; then
    log "too old lock, remove"
    rm $lock
  else
    log "exit"
    exit
  fi
fi

touch $lock
for acc in `curl -s "http://${host}/api/site/all"`; do
  log "start acc=$acc"
  curl -s "http://${host}/cron/rssGet2?acc=$acc"
  curl -s "http://${host}/cron/rssPost2?acc=$acc"
  log "done acc=$acc"
done
rm $lock

log "cron end"