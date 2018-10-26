FROM  hacklab/php:7.0-apache
LABEL mantainer "Redelivre <contato@redelivre.org>"
ARG REDELIVRE_SSH_PASSPHRASE=some_key_pass
ARG REDELIVRE_SSH_PRIVATE=some_ssh_key

WORKDIR /var/www/html/
#COPY ["src", "/var/www/html"]
COPY ["./", "/var/www"]
RUN rmdir /var/www/html \ 
	&& ln -s /var/www/src /var/www/html
	
COPY ["./Docker/wp-config.php", "/var/www/html"]
COPY ["wp-scripts", "/var/www/html/wp-scripts"]

# Redelivre
#COPY ["scripts", "/var/www/scripts"]
WORKDIR /var/www

COPY ["scripts/ssh", "/root/.ssh"]
    
RUN apt-get update \
	&& apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libpng-dev \
        git\
	libxml2-dev \
	libcurl4-gnutls-dev \
    && docker-php-ext-install -j$(nproc) iconv mcrypt mysqli pdo pdo_mysql mbstring curl xml\
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd

RUN if [ "$REDELIVRE_SSH_PASSPHRASE" != "some_key_pass" ] ; then \
	chown root:root /root/.ssh \
	&& chmod 600 /root/.ssh/* \
	&& chmod 700 /root/.ssh \
	&& ssh-keyscan -H -t rsa gitlab.com >> ~/.ssh/known_hosts \
	&& echo '#!/usr/bin/expect -f' > /tmp/rlpass \
	&& echo 'spawn ssh-add /root/.ssh/id_rsa' >> /tmp/rlpass \
	&& echo 'expect "Enter passphrase for /root/.ssh/id_rsa:"' >> /tmp/rlpass \
	&& echo "send \"$REDELIVRE_SSH_PASSPHRASE\n\";" >> /tmp/rlpass \
	&& echo 'expect "Identity added: /root/.ssh/id_rsa (/root/.ssh/id_rsa)"' >> /tmp/rlpass \
	&& echo 'interact' >> /tmp/rlpass \
	&& chmod 700 /tmp/rlpass \
	&& apt install -y openssh-server expect \
	&& eval `ssh-agent -s` \
	&& /tmp/rlpass \
	;fi \
	&& if [ "$REDELIVRE_SSH_PRIVATE" != "some_ssh_key" ] ; then \
	chown root:root /root/.ssh \
	&& chmod 600 /root/.ssh/* \
	&& chmod 700 /root/.ssh \
	&& ssh-keyscan -H -t rsa gitlab.com >> ~/.ssh/known_hosts \
	&& echo "-----BEGIN OPENSSH PRIVATE KEY-----" > /root/.ssh/id_rsa \
	&& echo $REDELIVRE_SSH_PRIVATE >> /root/.ssh/id_rsa \
	&& echo "-----END OPENSSH PRIVATE KEY-----" >> /root/.ssh/id_rsa \
	&& rm /root/.ssh/id_rsa.pub \
	;fi \
	&& sh scripts/updatesubs.sh

RUN	mkdir -p src/wp-content/uploads \
	&& mkdir -p src/wp-content/plugins/si-captcha-for-wordpress/captcha/cache \
	&& mkdir -p src/wp-content/plugins/si-captcha-for-wordpress/captcha/temp \
	&& mkdir -p src/wp-content/cache \
	&& mkdir -p src/wp-content/blogs.dir \
	&& mkdir -p src/wp-content/w3tc-config \
	&& chown -R "$APACHE_RUN_USER:$APACHE_RUN_GROUP" src/wp-content/uploads \
	src/wp-content/plugins/si-captcha-for-wordpress/captcha/cache \
	src/wp-content/plugins/si-captcha-for-wordpress/captcha/temp \
	src/wp-content/cache \
	src/wp-content/w3tc-config \
	src/wp-content/blogs.dir
