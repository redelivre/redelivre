#!/bin/bash

PWDAtual=`pwd`

git pull;

for l in $(git submodule|grep -v wordpress-xrds-simple|grep -v wordpress-openid|grep -v mapasdevista |awk {'print $2;'}); do
        cd $PWDAtual/$l;
        git checkout master;
        git pull;
done
cd $PWDAtual/src/wp-content/plugins/mapasdevista;
git checkout redelivre;
git pull;
cd $PWDAtual/src/wp-content/plugins/wp-opauth
git submodule update --init;
cd $PWDAtual
