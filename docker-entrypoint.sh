#!/bin/sh

: "${PORT:=80}"

sed -ri -e "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri -e "s/:80>/:${PORT}>/" /etc/apache2/sites-available/*.conf

exec "$@"