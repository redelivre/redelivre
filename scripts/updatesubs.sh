#!/bin/bash

PWDAtual=`pwd`

git pull;

for l in $(git submodule|grep -v wordpress-xrds-simple|grep -v wordpress-openid|grep -v mapasdevista |awk {'print $2;'}); do
	echo "Updating $l"
        cd $PWDAtual/$l;
        git checkout master;
        git pull;
done
echo "Updating Mapasdevista on branch Redelivre"
cd $PWDAtual/src/wp-content/plugins/mapasdevista;
git checkout redelivre;
git pull;

if [ ! -e PWDAtual/src/wp-content/plugins/wp-opauth/opauth/lib ] ; then
	cd $PWDAtual/src/wp-content/plugins/wp-opauth
	git submodule update --init
fi

cd $PWDAtual/src/wp-content/plugins/wordpress-openid
git checkout anyone
git pull

cd $PWDAtual
