#!/bin/sh
git submodule init
git submodule update
mkdir tmp
chmod 0777 tmp
cp keys.php.skeleton keys.php
cp tokens.tsv.skeleton tokens.tsv
