FROM php:7.2.12-apache

ENV USER="ergo" \
    GROUP="ergo" \
    UID="1000" \
    GID="1000"

ADD /src /var/www/html

###
### User/Group
###
RUN set -x \
	&& groupadd -g ${GID} -r ${GROUP} \
	&& useradd -u ${UID} -m -s /bin/bash -g ${GROUP} ${USER}

###
### Update
###
RUN set -x \
  && apt-get update \
  && apt-get install --no-install-recommends --no-install-suggests -y \
    unzip \
    zlib1g-dev \
  && docker-php-ext-install zip \
  && docker-php-ext-enable zip



###
### Install PDO
###
RUN set -x \
   && docker-php-ext-install pdo_mysql \
   && docker-php-ext-enable pdo_mysql

###
### Install composer
###
RUN set -x \
  && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
  && composer self-update

###
### Adding custom vhost conf
###
ADD /vhost/vhost.conf /etc/apache2/sites-available

###
### Override default vhost conf
###
RUN set -x \
  # disable default vhost conf && enable custom vhost
  && a2dissite 000-default.conf \
  && a2ensite vhost.conf \
  && a2enmod rewrite

###
### Init project and fix permission
###
RUN set -x \
  && mkdir -p /var/www/html/public \
  && mkdir -p /var/www/html/logs \
  && chmod 0755 /var/www/html/public \
  && chmod -R 777 /var/www/html/logs \
  && chown -R ${USER}:${GROUP} /var/www/html

RUN set -x service apache2 restart

VOLUME /var/www/html

EXPOSE 80

WORKDIR /var/www/html
