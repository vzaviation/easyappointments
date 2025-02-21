#!/bin/bash
#
#  crontab line to run this:
#  20 * * * * /usr/bin/flock -n /tmp/vl-update.lockfile /bin/bash /var/www/html/load-inmate-updates/vl-data-update/run_vl_update_inmate_data.sh > /var/www/html/load-inmate-updates/vl-data-update/run_vl_update_inmate_data.log 2>&1
# 
cd /var/www/html/load-inmate-updates/vl-data-update
/usr/local/bin/node ./VL-update-inmate-data.js
