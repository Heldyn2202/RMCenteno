# Imagen base con PHP y Apache
FROM php:8.2-apache

# Instalar dependencias del sistema necesarias
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \        # 👈 ESTA ES LA CLAVE
    zip \
    unzip \
    libonig-dev \
    libxml2-dev \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mysqli gd mbstring xml zip \
    && a2enmod rewrite \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Copiar el proyecto
COPY . /var/www/html/

# Permisos adecuados
RUN chown -R www-data:www-data /var/www/html

# Exponer el puerto 80
EXPOSE 80

# Comando de inicio
CMD ["apache2-foreground"]
