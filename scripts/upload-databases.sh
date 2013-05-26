#! /bin/sh

rootDBFolder="/Shared Webmaster/Database Exports/"

uploadDirectory="$rootDBFolder`date +'%Y'`/`date +'%m.%d.%y.%H%M'`/"

./dropbox_uploader.sh mkdir "$uploadDirectory"


for arg in "$@"
do
	./dropbox_uploader.sh upload "$arg" "${uploadDirectory}$arg"
done