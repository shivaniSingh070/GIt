#!/bin/bash

# FOR All SYSTEMS.
#
# SH script to delete all the Navision order export logs txt files older than one day
# PM PS 16.05.2020 | https://trello.com/c/rhUrnEzW/99-2020-02-9-bestellung-fehlt-nach-erfolgreicher-bezahlung-missing-order-but-completed-credit-card-payment
#

PHP_BIN="/usr/local/bin/php"

# Benchmark.
SECONDS=0
now=$(date +"%T")

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

cd $DIR/../var/log/

# Loop through all navison txt files, delete them older than one day
	find . -type f -name "*.txt" -mtime +1 -exec rm -fv {} \;

 echo "======================================"
 now=$(date +"%T")
 duration=$SECONDS
 echo "Navison Order Export txt files deleted older than 1 day: $now"
