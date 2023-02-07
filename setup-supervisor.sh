#!/bin/bash

PROJECT_PATH="$( cd -- "$(dirname "$0")" >/dev/null 2>&1 || exit ; pwd -P )"

SUPERVISOR_FILE=/etc/supervisor/conf.d/swap-worker.conf

cat > $SUPERVISOR_FILE <<- EOM
[program:swap-worker]
process_name=%(program_name)s_%(process_num)02d
command=php $PROJECT_PATH/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=8
redirect_stderr=true
stdout_logfile=$PROJECT_PATH/worker.log
stopwaitsecs=3600
EOM

sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start swap-worker:*
