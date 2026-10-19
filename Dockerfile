FROM php:8.2-apache

# Install PostgreSQL client & PDO PHP extension
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy project files into web server root
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html/

EXPOSE 80
