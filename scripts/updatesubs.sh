#!/bin/bash

PWDAtual=`pwd`
echo "Working On: $PWDAtual"
APACHEUSER=""

if [ hash apachectl 2>/dev/null ]; then
        APACHEUSER=`apachectl -S|grep User|awk '{print $2;}'|sed 's/name=//;s/\"//g'`
else
        APACHEUSER=www-data
fi
echo "Apache user is: $APACHEUSER"
ROOTREPOS=`git config --get remote.origin.url`
checkIfRootRepos() {
        REPOS=`git config --get remote.origin.url`
        if [ "$REPOS" = "$ROOTREPOS" ]; then
                return 1;
        fi
        return 0;
}

git pull;

for l in $(git submodule |grep ^-|awk '{print $2}'); do git submodule update --init $l; done

for l in $(git submodule|grep -v mapasdevista| grep -v recid|grep -v praticas-de-continuidade|grep -v observatorio-de-remocoes |awk {'print $2;'}); do
	echo "Updating $l"
        cd $PWDAtual/$l;
	if [ ! $(checkIfRootRepos) = 1 ]; then
	        git checkout master;
        	git pull;
	fi
done
echo "Updating Mapasdevista on branch Redelivre"
cd $PWDAtual/src/wp-content/plugins/mapasdevista;
if [ ! $(checkIfRootRepos) = 1 ]; then
	git checkout pontosdecultura;
	git pull;
fi

if [ ! -e $PWDAtual/src/wp-content/plugins/wp-opauth/opauth/lib ] ; then
	cd $PWDAtual/src/wp-content/plugins/wp-opauth
	git submodule update --init
fi

cd $PWDAtual/src/wp-content/themes/recid
if [ ! $(checkIfRootRepos) = 1 ]; then
	git checkout recid
	git pull
fi

cd $PWDAtual/src/wp-content/themes/praticas-de-continuidade
if [ ! $(checkIfRootRepos) = 1 ]; then
	git checkout praticas-de-continuidade
	git pull
fi

cd $PWDAtual/src/wp-content/themes/observatorio-de-remocoes
if [ ! $(checkIfRootRepos) = 1 ]; then
	git checkout observatorio-de-remocoes
	git pull
fi

cd $PWDAtual/src/wp-content/plugins/sendpress
if [ ! $(checkIfRootRepos) = 1 ]; then
	git checkout 1.8.3.30
	git pull
fi

cd $PWDAtual/src/wp-content/themes/wp-divi-3
if [ ! $(checkIfRootRepos) = 1 ]; then
	git checkout divi-3.0-version
	git pull
fi

EU=`id -u`

if [ -d $PWDAtual/src/wp-content/plugins/facebook-instant-articles-wp ] ; then
    echo "init $PWDAtual/src/wp-content/plugins/facebook-instant-articles-wp"
    cd $PWDAtual/src/wp-content/plugins/facebook-instant-articles-wp
    if [ $EU -ne 0 ]; then
        composer install
    else
    	if [ -f /.dockerenv ]; then
    		composer install
    	else
	        if [ ! -z "$APACHEUSER" ]; then
	            sudo -i -u $APACHEUSER composer install
	        else
	            echo composer need to be run by apache user or other
	            exit 1
	        fi
		fi
    fi
fi

if [ -d $PWDAtual/src/wp-content/themes/wp-logincidadao ] ; then
    if [ ! -d $PWDAtual/src/wp-content/themes/wp-logincidadao/login-cidadao ] ; then
	cd $PWDAtual/src/wp-content/themes/wp-logincidadao
	git submodule update --init
        cd login-cidadao
	if [ ! $(checkIfRootRepos) = 1 ]; then
	        git checkout master
	        git pull
	fi
    fi
fi

if [ ! -z "$APACHEUSER" ]; then
    if ( id -u $APACHEUSER >/dev/null 2>&1 ) ; then
        mkdir -p $PWDAtual/src/wp-content/plugins/si-captcha-for-wordpress/captcha/cache src/wp-content/plugins/si-captcha-for-wordpress/captcha/temp
	if [ "$EUID" -ne 0 ]; then
	        sudo chown -R $APACHEUSER $PWDAtual/src/wp-content/plugins/si-captcha-for-wordpress/captcha/cache src/wp-content/plugins/si-captcha-for-wordpress/captcha/temp
		sudo chown -R $APACHEUSER $PWDAtual/src/wp-content/uploads
		if [ -d $PWDAtual/src/wp-content/blogs.dir ]; then
			sudo chown -R $APACHEUSER $PWDAtual/src/wp-content/blogs.dir
		fi
	else
		chown -R $APACHEUSER $PWDAtual/src/wp-content/plugins/si-captcha-for-wordpress/captcha/cache src/wp-content/plugins/si-captcha-for-wordpress/captcha/temp
		chown -R $APACHEUSER $PWDAtual/src/wp-content/uploads
		if [ -d $PWDAtual/src/wp-content/blogs.dir ]; then
                        chown -R $APACHEUSER $PWDAtual/src/wp-content/blogs.dir
                fi
	fi
    fi
fi

cd $PWDAtual

