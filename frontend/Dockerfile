# Usar la última imagen oficial de PHP con Apache
FROM php:8.2-apache

# Instalar dependencias necesarias
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd mbstring zip pdo pdo_mysql mysqli

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar el directorio de trabajo
WORKDIR /var/www/html

# Copiar los archivos de la aplicación
COPY ./app /var/www/html

# Instalar las dependencias de Composer
#RUN composer install

# Configurar permisos de los archivos y directorios
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html

# Copiar configuración de Apache
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf

# Habilitar módulos de Apache necesarios
RUN a2enmod rewrite

# Exponer el puerto 8080
EXPOSE 80

# Iniciar el servidor Apache
CMD ["apache2-foreground"]

