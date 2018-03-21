FROM  hacklab/php:7.0-apache
LABEL mantainer "Hacklab <contato@hacklab.com.br>"

WORKDIR /var/www/html/
COPY ["src", "/var/www/html"]
COPY ["./Docker/wp-config.php", "/var/www/html"]
COPY ["wp-scripts", "/var/www/html/wp-scripts"]

# Redelivre
