#!/bin/bash

TRANSIFEX_BASE=/home/matt/habari/transifex/
TRANSIFEX_TS=/home/matt/habari/transifex/translations/habari.systemhead/
GIT=/home/matt/habari/habari-locales/
GIT_CRED=`cat /home/matt/habari-locales.userpass`

function github_create
{
	curl -f -u $GIT_CRED https://api.github.com/orgs/habari-locales/repos -d '{"name":"'$1'"}'
}

function github_clone
{
	git clone git@github.com:habari-locales/$1.git
}

function github_init
{
	mkdir $1
	mkdir $1/LC_MESSAGES
	cd $1
	touch README.md
	git init
	git add README.md
	git commit -m "intialize repo"
	git remote add origin git@github.com:habari-locales/$1.git
	git push -u origin master
	cd ..
}

function github_pull
{
	cd $1
	git pull
	cd ..
}

function github_push
{
	echo "moving $1.po"
	cd $1
	cp $TRANSIFEX_TS$1.po LC_MESSAGES/habari.po
	echo "generating mo file"
	msgfmt -o LC_MESSAGES/habari.mo LC_MESSAGES/habari.po
	git add LC_MESSAGES/habari.po LC_MESSAGES/habari.mo
	echo "committing to github"
	git commit -a -m"transifex sync"
	git push
	cd ../
}


# update transifex files
cd $TRANSIFEX_BASE;
tx pull;


# check fo .po
cd $GIT;
for d in `ls $TRANSIFEX_TS*.po`;
do
	if [[ $d =~ $TRANSIFEX_TS(.*).po ]]
	then
		if [ ! -d ${BASH_REMATCH[1]} ]
		then
			if ! github_create ${BASH_REMATCH[1]}
			then
				github_clone ${BASH_REMATCH[1]}
			else
				github_init ${BASH_REMATCH[1]}
			fi
		else
			github_pull ${BASH_REMATCH[1]}
		fi
		github_push ${BASH_REMATCH[1]}
	fi
done

echo "done!"
