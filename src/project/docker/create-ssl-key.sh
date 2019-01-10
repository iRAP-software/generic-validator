#!/bin/bash

# Get path of this script's folder
DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )  

a2enmod ssl
service apache2 restart
mkdir /etc/apache2/ssl 

if [ ! -f /var/www/validator/settings/ssl/certificate.crt ]; then
    echo "generating ssl keys since they weren't provided."
    openssl \
    req -x509 \
    -nodes \
    -days 365 \
    -newkey rsa:4096 \
    -keyout /etc/apache2/ssl/apache.key \
    -out /etc/apache2/ssl/apache.crt \
    -subj "/C=GB/ST=London/L=London/O=Global Security/OU=IT Department/CN=common.name"
else
    mv /var/www/validator/settings/ssl/private.key /etc/apache2/ssl/private.key
    mv /var/www/validator/settings/ssl/certificate.crt /etc/apache2/ssl/certificate.crt
    mv /var/www/validator/settings/ssl/ca_bundle.crt /etc/apache2/ssl/ca_bundle.crt
fi

SCRIPT=$(readlink -f "$0")
SCRIPTPATH=$(dirname "$SCRIPT") 
cd $SCRIPTPATH

mv $DIR/apache-ssl-config.conf /etc/apache2/sites-available/default-ssl.conf

a2ensite default-ssl
service apache2 reload
