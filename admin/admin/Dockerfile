 # ============================================================================
# 🧱 Imagen base de PHP con Apache
# ============================================================================
FROM php:8.2-apache  

# ============================================================================
# 🛠 Instalar extensiones necesarias (MariaDB/MySQL, PDO, ZIP, etc.)
# ============================================================================
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    libonig-dev \
    libxml2-dev \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mysqli gd mbstring xml zip \
    && a2enmod rewrite

# ============================================================================
# 🧩 Copiar todos los archivos del proyecto al contenedor
# ============================================================================
COPY . /var/www/html/

# ============================================================================
# ⚙️ Configurar el entorno de Apache
# ============================================================================
WORKDIR /var/www/html/
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# ============================================================================
# 🧰 Instalar Composer (para PHPMailer y dependencias)
# ============================================================================
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader || true

# ============================================================================
# 🌍 Variables de entorno para Render o Docker Compose
# ============================================================================
ENV RENDER=true
ENV APACHE_DOCUMENT_ROOT=/var/www/html

# ============================================================================
# 🔐 Habilitar HTTPS opcional (si Render lo usa)
# ============================================================================
EXPOSE 80

# ============================================================================
# 🏁 Comando de inicio
# ============================================================================
CMD ["apache2-foreground"]
