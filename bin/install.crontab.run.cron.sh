#!/bin/sh

echo Setting up crontab $1
/usr/bin/crontab -u www-data $1

echo Running cron daemon
/usr/sbin/cron -f -l 8
