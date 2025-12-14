#Selecciona la imagen base oficial de PHP 8.2 con FPM (FastCGI Process Manager). FPM es el proceso que ejecuta PHP y que Nginx usará vía FastCG
FROM php:8.2-fpm
#Ejecuta dentro de la imagen la instalación de extensiones PHP, es obligatorio para que PDO pueda comunicarse con MySQL.
RUN docker-php-ext-install pdo pdo_mysql
# Define el directorio de trabajo dentro del contenedor. Todas las instrucciones siguientes que usen rutas relativas partirán de aquí.
WORKDIR /var/www/app
#Copia todo el contenido del contexto de build (la carpeta donde esté el Dockerfile) al WORKDIR del contenedor.
COPY . .
#Declara que el contenedor escucha en el puerto 9000 (puerto estándar de PHP-FPM).
EXPOSE 9000
#Comando que se ejecuta cuando el contenedor arranca: inicia PHP-FPM (el servidor de procesos PHP).
#Nginx consultará a PHP-FPM en app:9000.
CMD ["php-fpm"]