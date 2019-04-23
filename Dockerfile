FROM  hacklab/php:7.0-apache
LABEL mantainer "Redelivre <contato@redelivre.org>"
ARG REDELIVRE_SSH_PASSPHRASE=some_key_pass
ARG REDELIVRE_SSH_PRIVATE=some_ssh_key
ARG WORDPRESS_UPGRADE_USER=some_user
ARG WORDPRESS_UPGRADE_PASS=some_pass
ARG DOSEmailNotify=some_email
ARG DOSSystemCommand=some_command

WORKDIR /var/www/html/
#COPY ["src", "/var/www/html"]
COPY ["./", "/var/www"]
RUN rmdir /var/www/html \ 
	&& ln -s /var/www/src /var/www/html
	
COPY ["./Docker/wp-config.php", "/var/www/html"]
COPY ["wp-scripts", "/var/www/html/wp-scripts"]
COPY ["./Docker/wordpress.conf", "/etc/apache2/conf-available/wordpress.conf"]

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
		libapache2-mod-evasive \
		libapache2-mod-security2 \
		unzip\
	&& docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) iconv mcrypt mysqli pdo pdo_mysql mbstring curl xml gd soap \ 
    && a2enmod rewrite \
    && a2enmod evasive \
    && a2enmod security2 \
    && touch /etc/apache2/wpupgrade.passwd \
    && a2enconf wordpress \
    && chown root:root /root/.ssh \
	&& chmod 600 /root/.ssh/* \
	&& chmod 700 /root/.ssh \
	&& if [ "$REDELIVRE_SSH_PASSPHRASE" != "some_key_pass" ] ; then \
		ssh-keyscan -H -t rsa gitlab.com >> ~/.ssh/known_hosts \
		&& echo '#!/usr/bin/expect -f' > /var/www/scripts/rlpass \
		&& echo 'spawn ssh-add /root/.ssh/id_rsa' >> /var/www/scripts/rlpass \
		&& echo 'expect "Enter passphrase for /root/.ssh/id_rsa:"' >> /var/www/scripts/rlpass \
		&& echo "send \"$REDELIVRE_SSH_PASSPHRASE\n\";" >> /var/www/scripts/rlpass \
		&& echo 'expect "Identity added: /root/.ssh/id_rsa (/root/.ssh/id_rsa)"' >> /var/www/scripts/rlpass \
		&& echo 'interact' >> /var/www/scripts/rlpass \
		&& chmod 700 /var/www/scripts/rlpass \
		&& apt install -y openssh-client expect \
		&& eval `ssh-agent -s` \
		&& /var/www/scripts/rlpass \
	;fi \
	&& if [ "$REDELIVRE_SSH_PASSPHRASE" != "some_key_pass" ] ; then \
		apt -y remove expect openssh-client \
		&& apt -y autoremove \
		&& rm /var/www/scripts/rlpass \
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
	&& sh scripts/updatesubs.sh \
	&& mkdir -p src/wp-content/uploads \
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
	src/wp-content/blogs.dir \
	&& if [ "$WORDPRESS_UPGRADE_USER" != "some_user" ] ; then \
		htpasswd -b /etc/apache2/wpupgrade.passwd $WORDPRESS_UPGRADE_USER $WORDPRESS_UPGRADE_PASS \
    ;fi \
    && cp /etc/modsecurity/modsecurity.conf-recommended /etc/modsecurity/modsecurity.conf \
    && sed -i 's/SecRuleEngine DetectionOnly/SecRuleEngine DetectionOnly/' /etc/modsecurity/modsecurity.conf \
    && sed -i 's/#DOS/DOS/g' /etc/apache2/mods-available/evasive.conf \
    && mkdir /var/log/mod_evasive \
    && if [ "$DOSSystemCommand" != "some_command" ] ; then \
    	 cat  /etc/apache2/mods-available/evasive.conf|awk -v DOSSystemCommand="$DOSSystemCommand" '{if($1 == "DOSSystemCommand"){print "    "$1"\t\""DOSSystemCommand"\"";} else {print $0;}}' > /etc/apache2/mods-available/evasive.conf.new \
    	 && mv /etc/apache2/mods-available/evasive.conf.new /etc/apache2/mods-available/evasive.conf \
	;else \
		sed -i 's/DOSSystemCommand/\#DOSSystemCommand/g' /etc/apache2/mods-available/evasive.conf \
	;fi \
	&& if [ "$DOSEmailNotify" != "some_email" ] ; then \
		sed -i "s/you@yourdomain.com/$DOSEmailNotify/" /etc/apache2/mods-available/evasive.conf \
	;else \
		sed -i "s/DOSEmailNotify/\#DOSEmailNotify/" /etc/apache2/mods-available/evasive.conf \
	;fi
	
	
	
	
