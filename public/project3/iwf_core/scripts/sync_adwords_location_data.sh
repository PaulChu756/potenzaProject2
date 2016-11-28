#!/bin/bash

# Configure this to execute nightly. Run as root to ensure file permissions can be changed.

WGET=$(which wget)
MYSQL=$(which mysql)
CHOWN=$(which chown)

echo "Downloading location data from AdWords..."
$WGET http://goo.gl/HndLIn -O /tmp/adwords_locations.csv
echo "Fixing permissions..."
$CHOWN mysql:mysql /tmp/adwords_locations.csv
echo "DONE!"
exit 0
