FROM php:7.0

RUN apt-get update && apt-get install libpng-dev mysql-client git unzip -y

RUN docker-php-ext-install mbstring

RUN docker-php-ext-install gd

RUN docker-php-ext-install zip

RUN docker-php-ext-install mysqli

RUN docker-php-ext-install pdo

RUN docker-php-ext-install pdo_mysql

RUN pecl install xdebug

RUN echo "zend_extension = /usr/local/lib/php/extensions/$(ls /usr/local/lib/php/extensions | tail -n 1)/xdebug.so" > /usr/local/etc/php/conf.d/xdebug.ini

RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar && chmod +x wp-cli.phar  && mv wp-cli.phar /usr/local/bin/wp

RUN mkdir -p /tmp/wordpress && wp core download --path=/tmp/wordpress --allow-root

RUN curl -O https://getcomposer.org/composer.phar && chmod +x composer.phar && mv composer.phar /usr/local/bin/composer