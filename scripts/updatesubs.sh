#!/bin/bash

PWDAtual=`pwd`
APACHEUSER=`apachectl -S|grep User|awk '{print $2;}'|sed 's/name=//;s/\"//g'`

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

cd $PWDAtual/src/wp-content/themes/recid
git checkout recid
git pull

cd $PWDAtual/src/wp-content/themes/praticas-de-continuidade
git checkout praticas-de-continuidade
git pull

cd $PWDAtual/src/wp-content/themes/observatorio-de-remocoes
git checkout observatorio-de-remocoes
git pull

cd $PWDAtual/src/wp-content/plugins/sendpress
git checkout 1.8.3.30
git pull

cd $PWDAtual/src/wp-content/themes/wp-divi-3
git checkout divi-3.0-version
git pull

if [ -d PWDAtual/src/wp-content/plugins/facebook-instant-articles-wp ] ; then
    cd $PWDAtual/src/wp-content/plugins/facebook-instant-articles-wp
    if [[ $EUID -ne 0 ]]; then
        composer install
    else
        if [ ! -z "$APACHEUSER" ]; then
            mkdir 
            sudo -i -u $APACHEUSER composer install
        else
            echo composer need to be run by apache user or other
        fi
    fi
fi

if [ -d PWDAtual/src/wp-content/themes/wp-logincidadao ] ; then
    if [ ! -d PWDAtual/src/wp-content/themes/wp-logincidadao/login-cidadao ] ; then
	cd $PWDAtual/src/wp-content/themes/wp-logincidadao
	git submodule update --init
        cd login-cidadao
        git checkout master
        git pull
    fi
fi

if [ ! -z "$APACHEUSER" ]; then
    if ( id -u $APACHEUSER >/dev/null 2>&1 ) ; then
        mkdir -p $PWDAtual/src/wp-content/plugins/si-captcha-for-wordpress/captcha/cache src/wp-content/plugins/si-captcha-for-wordpress/captcha/temp
        chown -R $APACHEUSER $PWDAtual/src/wp-content/plugins/si-captcha-for-wordpress/captcha/cache src/wp-content/plugins/si-captcha-for-wordpress/captcha/temp
    fi
fi

cd $PWDAtual

