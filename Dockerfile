FROM php:8.2-apache

# Instala dependências
RUN apt-get update && apt-get install -y \
  libssl-dev \
  && docker-php-ext-install sockets

# Instala o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Instala a biblioteca PHP AMQP
RUN docker-php-ext-install mysqli

# Instala dependências da aplicação
WORKDIR /var/www/html
COPY . /var/www/html
RUN composer install --no-dev --optimeze-autoloader

# Ativa o mod_rewrite do Apache
RUN a2enmod rewrite

EXPOSE 80
