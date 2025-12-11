# Imagen oficial de PHP con Apache
FROM php:8.2-apache

# Instalamos las extensiones necesarias para MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Directorio de trabajo de Apache
WORKDIR /var/www/html

# Copiamos todo el proyecto al directorio público de Apache
COPY . /var/www/html

# (Opcional) Habilitar mod_rewrite si llegas a usar URLs amigables
RUN a2enmod rewrite

# Puerto en el que escuchará Apache dentro del contenedor
EXPOSE 80
