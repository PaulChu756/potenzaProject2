#a work in progress. ultimately this should be a one-stop shop for fixing permissions on a core installation
find -type f -exec chown root:www-data {} \;
find -type d -exec chown root:www-data {} \;
find -type f -exec chmod 744 {} \;
find -type d -exec chmod 755 {} \;
chmod 775 /var/www/html/application/cache
chmod 775 /var/www/html/application/locks
chmod 775 /var/www/html/library/Ia/Entity/Proxy
chmod 775 /var/www/html/public/uploads
