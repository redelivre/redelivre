FROM  hacklab/php:7.0-apache
LABEL mantainer "Redelivre <contato@redelivre.org.br>"

WORKDIR /var/www/html/
#COPY ["src", "/var/www/html"]
COPY ["./", "/var/www"]
RUN ln -s /var/www/src /var/www/html
COPY ["./Docker/wp-config.php", "/var/www/html"]
COPY ["wp-scripts", "/var/www/html/wp-scripts"]

# Redelivre
#COPY ["scripts", "/var/www/scripts"]
WORKDIR /var/www
RUN apt-get update && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libpng-dev \
        git\
	libxml2-dev \
	libcurl4-gnutls-dev \
    && docker-php-ext-install -j$(nproc) iconv mcrypt mysqli pdo pdo_mysql mbstring curl xml\
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd \
	&& sh scripts/updatesubs.sh \
	&& ([ -d src/wp-content/uploads ] || mkdir -p src/wp-content/uploads) \
	&& ([ -d src/wp-content/plugins/si-captcha-for-wordpress/captcha/cache ] || mkdir -p src/wp-content/plugins/si-captcha-for-wordpress/captcha/cache) \
	&& ([ -d src/wp-content/plugins/si-captcha-for-wordpress/captcha/temp ] || mkdir -p src/wp-content/plugins/si-captcha-for-wordpress/captcha/temp) \
	&& ([ -d src/wp-content/cache ] || mkdir -p src/wp-content/cache) \
	&& ([ -d src/wp-content/blogs.dir ] || mkdir -p src/wp-content/blogs.dir) \
	&& ([ -d src/wp-content/w3tc-config ] || mkdir -p src/wp-content/w3tc-config) \
	&& chown -R "$APACHE_RUN_USER:$APACHE_RUN_GROUP" src/wp-content/uploads \
	src/wp-content/plugins/si-captcha-for-wordpress/captcha/cache \
	src/wp-content/plugins/si-captcha-for-wordpress/captcha/temp \
	src/wp-content/cache \
	src/wp-content/w3tc-config \
	src/wp-content/blogs.dir