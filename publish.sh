#!/bin/bash

src=/Users/andy/src/websites/cbi
dst=/Users/andy/Sites/cbi/htdocs

home=`pwd`

rsync -av --exclude .git . $dst
