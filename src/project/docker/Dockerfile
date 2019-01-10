FROM ubuntu:16.04

RUN apt-get update
RUN apt-get dist-upgrade -y


# Install the relevant packages
RUN apt-get install vim apache2 libapache2-mod-php7.0 php7.0-cli php7.0-mysql curl php7.0-curl \
            supervisor php7.0-mbstring php7.0-bcmath php7.0-xml -y

# Enable the php mod we just installed
RUN a2enmod php7.0


# expose port 80 and 443 for the web requests
EXPOSE 80
EXPOSE 443


###### Update the php INI settings #########

# Increase php's max allowed memory size
RUN sed -i 's;memory_limit = .*;memory_limit = -1;' /etc/php/7.0/apache2/php.ini
RUN sed -i 's;memory_limit = .*;memory_limit = -1;' /etc/php/7.0/cli/php.ini

RUN sed -i 's;display_errors = .*;display_errors = Off;' /etc/php/7.0/apache2/php.ini
RUN sed -i 's;display_errors = .*;display_errors = On;' /etc/php/7.0/cli/php.ini

# Set the max execution time
RUN sed -i 's;max_execution_time = .*;max_execution_time = 300;' /etc/php/7.0/apache2/php.ini
RUN sed -i 's;max_execution_time = .*;max_execution_time = 300;' /etc/php/7.0/cli/php.ini

# This is also needed for execution time
RUN sed -i 's;max_input_time = .*;max_input_time = 300;' /etc/php/7.0/apache2/php.ini


####### END of updating php INI ########
########################################

# Manually set the apache environment variables in order to get apache to work immediately.
ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_LOG_DIR /var/log/apache2

# It appears that the new apache requires these env vars as well
ENV APACHE_LOCK_DIR /var/lock/apache2
ENV APACHE_PID_FILE /var/run/apache2/apache2.pid

# Set up url rewrite ability
RUN a2enmod rewrite

# Install the cron service
RUN apt-get install cron -y

# Add our website's files to the default apache directory (/var/www)
# This should be as close to the last step as possible for faster rebuilds
ADD settings /var/www/validator/settings

# Add our website's files to the default apache directory (/var/www)
ADD project /var/www/validator/project

# Update our apache sites available with the config we created
ADD project/docker/apache-config.conf /etc/apache2/sites-enabled/000-default.conf

# Add our supervisor config to the container
COPY project/docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Configure apache to use a newly generate ssl key
# or generate one if there is no key in the files.
# This MUST go after we have added the project files to the container.
RUN /bin/bash /var/www/validator/project/docker/create-ssl-key.sh

# Use the crontab file.
# The crontab file was already added when we added "project"
RUN crontab /var/www/validator/project/docker/crons.conf

# Make the uploads folder writeable
RUN chown root:www-data -R /var/www
RUN chmod 750 -R /var/www/validator

# Execute the container's startup script which will start many processes/services
# The startup file was already added when we added "project"
CMD ["/bin/bash", "/var/www/validator/project/docker/startup.sh"]
