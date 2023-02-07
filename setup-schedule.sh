#!/bin/bash

PROJECT_PATH="$( cd -- "$(dirname "$0")" >/dev/null 2>&1 || exit ; pwd -P )"

CRON_PATH=/etc/crontab

# If use crontab -e, no need to add root
grep "* * * * * root cd \"$PROJECT_PATH\" && php artisan schedule:run >> /dev/null 2>&1" "$CRON_PATH" ||
echo "* * * * * root cd \"$PROJECT_PATH\" && php artisan schedule:run >> /dev/null 2>&1" >> "$CRON_PATH"
