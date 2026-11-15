# Image avec le serveur intégré Apache et PHP 8.3
FROM php:8.3-apache

# Extension PDO pour PostgreSQL, unzip pour décompresser les fichiers Composer
RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev unzip \
    && docker-php-ext-install pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

# Le reste du code n'est pas accessible le site est servi depuis le dossier public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/Public
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
/etc/apache2/sites-available/*.conf /etc/apache2/apache2.conf \
    && a2enmod rewrite

# Heure de Bordeaux (fuseau horaire)
RUN echo "date.timezone = Europe/Paris" > /usr/local/etc/php/conf.d/timezone.ini

# Composer : gestionnaire de dépendances PHP
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
