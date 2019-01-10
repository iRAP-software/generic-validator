# Please do not manually call this file!
# This script is run by the docker container when it is "run"

# Run the apache process in the background
#/usr/sbin/apache2 -D APACHE_PROCESS &

# Stop apache so that supervisor can start and manage it.
# leaving apache running will result in supervisor not managing the process.
service apache2 stop

# run migrations.
/usr/bin/php /var/www/validator/project/scripts/Migrate.php


# Run the supervisor script in the foreground to keep the container open.
/usr/bin/supervisord
