FROM php:8.2-apache

# Install PostgreSQL dependencies and enable PHP PDO PostgreSQL extensions
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql

# Fix Apache MPM conflict by explicitly disabling event/worker and enabling prefork
RUN a2dismod mpm_event mpm_worker || true && a2enmod mpm_prefork

# Enable Apache mod_rewrite for URL routing
RUN a2enmod rewrite

# Change Apache DocumentRoot to point to the 'public' folder
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Copy all project files into the container
COPY . /var/www/html/

# Ensure proper permissions for Apache
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expose port 80 for Render
EXPOSE 80
