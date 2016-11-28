#!/bin/bash

PWD=${PWD}
CURDIR=${PWD##*/}
GIT=$(which git)
PHP=$(which php)
RM=$(which rm)
PUSHD=$(which pushd)
POPD=$(which popd)
TOUCH=$(which touch)
APACHE_USER=$(ps axho user,comm|grep -E "httpd|apache"|uniq|grep -v "root"|awk 'END {if ($1) print $1}')

if [ $CURDIR != 'scripts' ]; then
     echo This script must be run from inside the scripts folder of your working directory
     exit 0
fi

echo "IWF Core Application Deployment"
echo "(c) 2015 Information ArchiTECH, LLC"
echo "Make sure the user you are running under is root or has sudo permissions (and you are ready with the password) before continuing."

read -p "USER for file ownership. developer is preferred. (Default $APACHE_USER): " apacheuser
apacheuser=${apacheuser:-$APACHE_USER}

read -p "GROUP for file ownership. (Default $APACHE_USER): " apachegroup
apachegroup=${apachegroup:-$APACHE_USER}

read -p "Ready to deploy? <y/N> " prompt
if [[ $prompt == "y" || $prompt == "Y" || $prompt == "yes" || $prompt == "Yes" ]]
then
  echo "Placing application in maintenance mode"
  $TOUCH $PWD/../maintenance.flag
  echo "Running GIT update on core"
  pushd $PWD/../ ; $GIT pull origin "`git branch | grep -E '^\* ' | sed 's/^\* //g'`" ; popd
  echo "Running GIT update on modules"
  pushd $PWD/../application/modules ; $GIT pull origin "`git branch | grep -E '^\* ' | sed 's/^\* //g'`" ; popd
  echo "Purging Proxy classes"
  $RM $PWD/../library/Ia/Entity/Proxy/* -rf
  echo "Clearing application cache"
  $RM $PWD/../application/cache/* -rf
  echo "Executing patch scripts/updating schema"
  $PHP $PWD/apply_patches.php
  echo "Graceful apache restart"
  sudo service apache2 graceful
  echo "Supervisor restart (where applicable)"
  sudo service supervisor restart
  echo "Touching document root to ensure assets are refreshed"
  touch $PWD/../
  echo "Setting file ownership"
  sudo chown $apacheuser:$apachegroup ../* -R
  echo "Taking application out of maintenance mode"
  rm $PWD/../maintenance.flag
  echo "Deployment complete!"
else
  echo Nothing done.
  exit 0
fi
