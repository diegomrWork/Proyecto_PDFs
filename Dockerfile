FROM php:8.2-apache

# 1. Instalar dependencias para Laravel, DomPDF y Node.js (para Vue)
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpng-dev libjpeg-dev libfreetype6-dev \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql zip gd

# 2. Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 3. Configurar Apache con permisos totales para las rutas de Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN echo "<VirtualHost *:80>\n\
    DocumentRoot \${APACHE_DOCUMENT_ROOT}\n\
    <Directory \${APACHE_DOCUMENT_ROOT}>\n\
        Options Indexes FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
        FallbackResource /index.php\n\
    </Directory>\n\
</VirtualHost>" > /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite

WORKDIR /var/www/html
COPY . .

# 4. Instalar librerías y compilar tu Vue
RUN composer install --optimize-autoloader --no-dev
RUN npm install
RUN npm run build

# 5. Permisos de escritura para los PDFs temporales
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80