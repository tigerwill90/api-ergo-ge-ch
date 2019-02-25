FROM php:7.2.15-apache

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
    git \
    openssh-client \
    zlib1g-dev \
    libgmp-dev \
    libsodium-dev \
  \
  && docker-php-ext-install zip \
  && docker-php-ext-enable zip \
  \
  && ln /usr/include/x86_64-linux-gnu/gmp.h /usr/include/ \
  && docker-php-ext-install gmp \
  && docker-php-ext-enable gmp \
  \
  && docker-php-ext-install sodium \
  && docker-php-ext-enable sodium

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
### Adding rsa key
###
RUN set -x \
    && mkdir /root/.ssh \
    && mkdir /home/${USER}/.ssh \
    && echo "IdentityFile /root/.ssh/id_rsa" >> /root/.ssh/config \
    && echo "IdentityFile /home/${USER}/.ssh/id_rsa" >> /home/${USER}/.ssh/config \
    && touch /root/.ssh/known_hosts \
    && touch /home/${USER}/.ssh/known_hosts
ADD id_rsa /root/.ssh
ADD id_rsa /home/${USER}/.ssh
RUN set -x \
    && chmod 700 /root/.ssh \
    && chmod 600 /root/.ssh/id_rsa \
    && chmod 600 /root/.ssh/config \
    && chmod 600 /root/.ssh/known_hosts \
    && chmod 700 /home/${USER}/.ssh \
    && chmod 600 /home/${USER}/.ssh/id_rsa \
    && chmod 600 /home/${USER}/.ssh/config \
    && chmod 600 /home/${USER}/.ssh/known_hosts \
    # requered for non-root user
    && chown -R ${USER}:${GROUP} /home/${USER}/.ssh

###
### Init project and fix permission
###
RUN set -x \
  && chmod 0755 /var/www/html/public \
  && chmod -R 777 /var/www/html/logs \
  && chown -R ${USER}:${GROUP} /var/www/html

RUN set -x service apache2 restart

VOLUME /var/www/html

EXPOSE 80 443

WORKDIR /var/www/html
