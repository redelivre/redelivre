#!/bin/bash

PWDAtual=`pwd`

git pull;

for l in $(git submodule |grep ^-|awk '{print $2}'); do git submodule update --init $l; done

for l in $(git submodule|grep -v wordpress-xrds-simple|grep -v wordpress-openid|grep -v mapasdevista| grep -v recid |awk {'print $2;'}); do
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

cd $PWDAtual/src/wp-content/plugins/wordpress-openid
git checkout anyone
git pull

cd $PWDAtual/src/wp-content/themes/recid
git checkout recid
git pull

cd $PWDAtual/src/wp-content/themes/praticas-de-continuidade
git checkout praticas-de-continuidade
git pull

cd $PWDAtual/src/wp-content/themes/observatorio-de-remocoes
git checkout observatorio-de-remocoes
git pull

if [ ! -d PWDAtual/src/wp-content/themes/wp-logincidadao/login-cidadao ] ; then
	cd $PWDAtual/src/wp-content/themes/wp-logincidadao
	git submodule update --init
        cd login-cidadao
        git checkout master
        git pull
fi

cd $PWDAtual
