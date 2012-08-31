#!/bin/bash

# setup tmp files from tar.gz's
cd /home/matt/habari-locales/bzr/
bzr merge
bzr add locale/*
bzr commit -m "lp sync"
cd ../

# check fo .po
for d in `ls bzr/locale/*.po`;
do
    if [[ $d =~ bzr/locale/(.*).po ]]
    then
        if [ ! -d ${BASH_REMATCH[1]} ]
        then
            mkdir  ${BASH_REMATCH[1]}
            mkdir ${BASH_REMATCH[1]}/LC_MESSAGES
            mkdir ${BASH_REMATCH[1]}/dist
            cd ${BASH_REMATCH[1]}
            curl -u '`cat /home/matt/habari-locales.userpass`' https://api.github.com/orgs/habari-locales/repos -d '{"name":"'${BASH_REMATCH[1]}'"}'
            touch README.md
            git init
            git add README.md
            git commit -m "intialize repo"
            git remote add origin git@github.com:Habari-Locales/${BASH_REMATCH[1]}.git
            git push -u origin master
            cd ../
        else
            cd ${BASH_REMATCH[1]}
            git pull
            cd ../
        fi
        echo "moving  ${BASH_REMATCH[1]}.po"
        cd ${BASH_REMATCH[1]}
        cp ../bzr/locale/${BASH_REMATCH[1]}.po LC_MESSAGES/habari.po
        echo "generating mo file"
        msgfmt -o LC_MESSAGES/habari.mo LC_MESSAGES/habari.po
        git add LC_MESSAGES/habari.po LC_MESSAGES/habari.mo dist
        git commit -a -m"launchpad sync"
        git push
        cd ../
    fi
done

echo "done!"

