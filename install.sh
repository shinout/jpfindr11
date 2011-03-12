#!/bin/sh
git submodule init
git submodule update
cp keys.php.skeleton keys.php
cp tokens.tsv.skeleton tokens.tsv
