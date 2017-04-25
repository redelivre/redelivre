#!/bin/bash

PWDAtual=`pwd`

git pull;

for l in $(git submodule |grep ^-|awk '{print $2}'); do git submodule update --init $l; done

for l in $(git submodule|grep -v mapasdevista| grep -v recid|grep -v praticas-de-continuidade|grep -v observatorio-de-remocoes |awk {'print $2;'}); do
	echo "Updating $l"
        cd $PWDAtual/$l;
        git checkout master;
        git pull;
done
echo "Updating Mapasdevista on branch Redelivre"
cd $PWDAtual/src/wp-content/plugins/mapasdevista;
git checkout pontosdecultura;
git pull;

if [ ! -e PWDAtual/src/wp-content/plugins/wp-opauth/opauth/lib ] ; then
	cd $PWDAtual/src/wp-content/plugins/wp-opauth
	git submodule update --init
fi

cd $PWDAtual/src/wp-content/plugins/sendpress
git checkout 1.7.12.15
git pull

cd $PWDAtual/src/wp-content/themes/wp-divi-3
git checkout divi-3.0-version
git pull

cd $PWDAtual/src/wp-content/plugins/facebook-instant-articles-wp
composer install

APACHEUSER=`apachectl -S|grep User|awk '{print $2;}'|sed 's/name=//;s/\"//g'`
if [ ! -z "$APACHEUSER" ]; then
    if ( id -u $APACHEUSER >/dev/null 2>&1 ) ; then
        mkdir -p $PWDAtual/src/wp-content/plugins/si-captcha-for-wordpress/captcha/cache src/wp-content/plugins/si-captcha-for-wordpress/captcha/temp
        chown -R $APACHEUSER $PWDAtual/src/wp-content/plugins/si-captcha-for-wordpress/captcha/cache src/wp-content/plugins/si-captcha-for-wordpress/captcha/temp
    fi
fi

cd $PWDAtual

