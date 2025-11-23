# Usa PHP con servidor embebido
FROM php:8.2-apache

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Copiar todo el proyecto al servidor
COPY . /var/www/html/

# Exponer la carpeta como root
WORKDIR /var/www/html/

# Abrir el puerto usado por Render
EXPOSE 10000

# Iniciar el servidor PHP embebido en Render
CMD ["php", "-S", "0.0.0.0:10000", "-t", "/var/www/html"]
