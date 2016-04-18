#!/bin/bash -e

logfile=indexation-$(date +%Y%m%d_%H%M%d).log

ln -sf "$logfile" indexation.log

(
 make --debug=b "$@" 2>&1|awk '{ print strftime("%Y-%m-%d %H:%M:%S"), $0; fflush(); }'
) >> "$logfile"
